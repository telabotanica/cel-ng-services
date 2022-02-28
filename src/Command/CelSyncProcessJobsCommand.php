<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Entity\Occurrence;
use App\Entity\PnTbPair;
use App\Model\AnnuaireUser;
use App\Service\AnnuaireService;
use App\Service\IdentiplanteService;
use App\Service\OccurrenceBuilderService;
use App\Service\PhotoBuilderService;
use App\Service\PhotoService;
use App\Service\PlantnetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

final class CelSyncProcessJobsCommand extends Command
{
    protected static $defaultName = 'cel:sync:process-jobs';

    private $em;
    private $pnTbPairRepository;
    private $occurrenceRepository;
    private $changeLogRepository;
    private $plantnetService;
    private $occurrenceBuilderService;
    private $photoBuilderService;
    private $identiplanteService;
    private $annuaireService;

    private $stats = [
        'ignored' => 0,
        'created' => 0,
        'updated' => 0,
        'deleted' => 0,
        'commented' => 0,
        'new photo' => 0,
    ];

    private $occurrencesToComment = [];

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        PlantnetService $plantnetService,
        OccurrenceBuilderService $occurrenceBuilderService,
        PhotoBuilderService $photoBuilderService,
        IdentiplanteService $identiplanteService,
        AnnuaireService $annuaireService
    ) {
        $this->em = $em;
        $this->pnTbPairRepository = $this->em->getRepository(PnTbPair::class);
        $this->occurrenceRepository = $this->em->getRepository(Occurrence::class);
        $this->changeLogRepository = $this->em->getRepository(ChangeLog::class);
        $this->plantnetService = $plantnetService;
        $this->occurrenceBuilderService = $occurrenceBuilderService;
        $this->photoBuilderService = $photoBuilderService;
        $this->identiplanteService = $identiplanteService;
        $this->annuaireService = $annuaireService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Process sync jobs, get occurrences infos and store them')
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                'If set, don’t persist changes to database')
            ->addOption('process-order', null, InputOption::VALUE_REQUIRED,
                'Order to take job, older first, or newer first', 'older')
            ->addOption('pn-occurrence-id', null, InputOption::VALUE_REQUIRED,
                'PlantNet occurrence ID to process (forced update/create)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('pn-sync-process-jobs');

        $dryRun = $input->getOption('dry-run');
        $processOrder = $input->getOption('process-order');
        if (!in_array($processOrder, ['older', 'newer'])) {
            $this->io->error('Jobs process order mode has to be either ’older’ (default) or ’newer’');
            return 1;
        }
        $pnOccurrenceId = $input->getOption('pn-occurrence-id');

        if ($pnOccurrenceId) {
            $jobs = $this->simulateJobs($pnOccurrenceId);
        } else {
            $jobs = $this->getJobs($processOrder);
        }

        /**
         * @var $job ChangeLog
         */
        foreach ($jobs as $job) {
            switch ($job->getActionType()) {
                case 'delete':
                    $occurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $job->getEntityId()]);
                    if ($occurrence) {
                        $pnTbPair = $this->pnTbPairRepository->findOneBy(['occurrence' => $occurrence]);
                        $this->em->remove($occurrence);
                        $this->em->remove($pnTbPair);

                        $this->stats['deleted']++;
                    } else {
                        $this->stats['ignored']++;
                    }

                    break;
                case 'update':
                    $this->updateOccurrence($job->getEntityId());
                    
                    break;
                case 'create':
                    $this->createOccurrence($job->getEntityId());

                    break;
                default:
                    throw new \Exception('Unsupported job action: %s', $job->getActionType());
                    break;
            }
            $this->em->remove($job);
        }

        if (!$dryRun) {
            $this->em->flush();
            // Need another flush to save Photo postPersist updates. Maybe there is some better way to do that
            $this->em->flush();

            foreach ($this->occurrencesToComment as $occurrenceToComment) {
                $this->identiplanteService->addComment($occurrenceToComment);
                $this->stats['commented']++;
            }
        }

        $event = $stopwatch->stop('pn-sync-process-jobs');
        if ($output->isVerbose()) {
            $this->io->success('Success!');
            foreach ($this->stats as $stat => $value) {
                $this->io->text(' '.ucfirst($stat).': '.$value);
            }
            $this->io->text(sprintf('  Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return 0;
    }

    private function getJobs(string $mode): array
    {
        $order = 'asc';
        if ($mode === 'newer') {
            $order = 'desc';
        }
        return $this->changeLogRepository->findBy(['entityName' => 'plantnet'], ['id' => $order], 50);
    }

    private function updateOccurrence(int $id): void
    {
        $pnOccurrence = $this->plantnetService->getOccurrenceById($id);
        if (!$pnOccurrence) {
            $this->stats['ignored']++;
            return;
        }
        if ($pnOccurrence->isDeleted() || $pnOccurrence->isCensored()) {
            $this->stats['ignored']++;
            return;
        }

        $occurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $id]);
        if (!$occurrence) {
            $this->stats['ignored']++;
            return;
        }

        $pnTbPair = $this->pnTbPairRepository->findOneBy(['occurrence' => $occurrence]);
        if (!$pnTbPair) {
            $this->stats['ignored']++;
            return;
        }
        if ($pnTbPair->getPlantnetOccurrenceUpdatedAt() >= $pnOccurrence->getDateUpdated()) {
            $this->stats['ignored']++;
            return;
        }

        // keep old sci name reference, easy to find if it changed after update
        $previousSciNameId = $occurrence->getAcceptedSciNameId();

        // update occurrence, all props are overwritten
        $occurrence = $this->occurrenceBuilderService->updateWithPlantnetOccurrence($occurrence, $pnOccurrence);
        $this->stats['updated']++;

        // if scientific name has changed, create new IP comment
        if ($previousSciNameId !== $occurrence->getAcceptedSciNameId()) {
            $this->occurrencesToComment[] = $occurrence;
        }

        // update photos
        foreach ($pnOccurrence->getImages() as $image) {
            if (!$occurrence->isExistingPhoto($image)) {
                $file = $this->plantnetService->getImageFile($image);
                $photo = $this->photoBuilderService->createPhoto($file, $occurrence);

                $this->em->persist($photo);
                $photosIds[] = $image->getId();
                $this->stats['new photo']++;
            }
        }
        // list photos, add new, remove deleted

        // update PnTbPair
        $pnTbPair->setPlantnetOccurrenceUpdatedAt($pnOccurrence->getDateUpdated());
    }

    private function createOccurrence(int $id): void
    {
        $occurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $id]);
        if ($occurrence) {
            $this->stats['ignored']++;
            return;
        }
        $pnOccurrence = $this->plantnetService->getOccurrenceById($id);
        if (!$pnOccurrence) {
            $this->stats['ignored']++;
            return;
        }
        if ($pnOccurrence->isDeleted() || $pnOccurrence->isCensored()) {
            $this->stats['ignored']++;
            return;
        }
        if (0 >= count($pnOccurrence->getImages())) {
            $this->stats['ignored']++;
            return;
        }

        /**
         * @var $user AnnuaireUser
         */
        $user = $this->annuaireService->findUserInfo($pnOccurrence->getAuthor()->getEmail());
        if (!$user) {
            // cannot find the user, its email may have changed, skipping
            $this->stats['ignored']++;
            return;
        }

        $occurrence = $this->occurrenceBuilderService->createOccurrence($user, $pnOccurrence);
        $occurrence = $this->occurrenceBuilderService->updateWithPlantnetOccurrence($occurrence, $pnOccurrence);

        $this->em->persist($occurrence);
        $this->stats['created']++;

        // create IP comment with current determination given by plantnet
        $this->occurrencesToComment[] = $occurrence;

        // create Photos
        foreach ($pnOccurrence->getImages() as $image) {
            $file = $this->plantnetService->getImageFile($image);
            $photo = $this->photoBuilderService->createPhoto($file, $occurrence);

            $this->em->persist($photo);
            $this->stats['new photo']++;
        }

        // tag plantnet-project ? No. No need to tag, we have inputSource column
    }

    private function simulateJobs(int $pnOccurrenceId): array
    {
        $pnOccurrence = $this->plantnetService->getOccurrenceById($pnOccurrenceId);
        if (!$pnOccurrence) {
            $this->io->error('Unknown PlantNet occurrence ID: '.$pnOccurrenceId);
            return [];
        }

        $changelog = new ChangeLog();
        $changelog->setEntityName('plantnet');
        $changelog->setEntityId($pnOccurrenceId);

        $occurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $pnOccurrence->getId()]);
        if (!$occurrence) {
            $changelog->setActionType('create');
        } else {
            $changelog->setActionType('update');

            $oldEnoughDate = new \DateTime('1312-01-01');
            $pnTbPair = $this->pnTbPairRepository->findOneBy(['occurrence' => $occurrence]);
            if ($pnTbPair) {
                $pnTbPair->setPlantnetOccurrenceUpdatedAt($oldEnoughDate);
            } else {
                $this->em->persist(new PnTbPair(
                    $occurrence,
                    $pnOccurrenceId,
                    $oldEnoughDate,
                ));
            }
            $this->em->flush();
        }

        return [
            $changelog
        ];
    }
}
