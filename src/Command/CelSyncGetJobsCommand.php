<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Entity\Occurrence;
use App\Model\PlantnetOccurrence;
use App\Model\PlantnetOccurrences;
use App\Service\AnnuaireService;
use App\Service\PlantnetPaginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

final class CelSyncGetJobsCommand extends Command
{
    protected static $defaultName = 'cel:sync:get-jobs';

    private $em;
    private $occurrenceRepository;
    private $changeLogRepository;
    private $annuaireService;
    private $plantnetPaginator;
    private $newJobsCount = 0;
    private $existingJobsCount = 0;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        AnnuaireService $annuaireService,
        PlantnetPaginator $plantnetPaginator
    ) {
        $this->em = $em;
        $this->occurrenceRepository = $this->em->getRepository(Occurrence::class);
        $this->changeLogRepository = $this->em->getRepository(ChangeLog::class);
        $this->annuaireService = $annuaireService;
        $this->plantnetPaginator = $plantnetPaginator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates sync jobs of PlantNet occurrences created by Telabotaniste')
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                'If set, don’t persist changes to database')
            ->addOption('from', null, InputOption::VALUE_REQUIRED,
                'Start date to read the occurrences history')
            ->addOption('email', null, InputOption::VALUE_REQUIRED,
                 'Filter on author email')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * We want to consider all occurrences (except the one from partners)
     * Even deleted or censored occurrences, because maybe we already have them in database and need to update
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('pn-sync-get-jobs');

        $dryRun = $input->getOption('dry-run');
        $startDate = (int) $input->getOption('from');
        $email = (string) $input->getOption('email');

        $this->plantnetPaginator->start($startDate, $email);

        do {
            $occurrences = $this->plantnetPaginator->getContent();
            if (!$occurrences) {
                break;
            }

            // Switch from PlantnetOccurrences to PlantnetOccurrence[]
            $occurrences = $occurrences->getData();
            foreach ($occurrences as $occurrence) {
                // filter out partners occurrences
                if ($occurrence->getPartner()) {
                    continue;
                }
                // already known occurrence ? need to update ? delete ?
                $existingOccurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $occurrence->getId()]);
                if ($existingOccurrence) {
                    if ($occurrence->isDeleted() || $occurrence->isCensored()) {
                        $this->addJob('delete', $occurrence->getId());
                    } else {
                        $this->addJob('update', $occurrence->getId());
                    }
                // we got a not known occurrence, is its author a Telabotaniste?
                } elseif ($this->annuaireService->isKnownUser($occurrence->getAuthor()->getEmail())) {
                    $this->addJob('create', $occurrence->getId());
                } // else we don't want to consider this occurrence
            }
        } while ($this->plantnetPaginator->nextPage());

        if (!$dryRun) {
            $this->em->flush();
        }

        $event = $stopwatch->stop('pn-sync-get-jobs');
        if ($output->isVerbose()) {
            $this->io->success(sprintf(
                'Success! Got %d new jobs, %d already know, out of %d total processed occurrences!',
                $this->newJobsCount, $this->existingJobsCount, count($occurrences)
            ));

            $this->io->comment(sprintf('Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return 0;
    }

    private function addJob(string $type, int $id)
    {
        if (!in_array($type, ['update', 'create', 'delete'])) {
            throw new \Exception('Job has be deleted, created or updated, nothing else');
        }

        $changelog = $this->changeLogRepository->findOneBy(['entityName' => 'plantnet', 'entityId' => $id]);
        if (!$changelog) {
            $changelog = new ChangeLog();
            $changelog->setActionType($type);
            $changelog->setEntityName('plantnet');
            $changelog->setEntityId($id);
            $this->em->persist($changelog);
            $this->newJobsCount++;

            if (0 === $this->newJobsCount % 50) {
                $this->em->flush();
            }
        } else {
            $this->existingJobsCount++;
        }
    }
}
