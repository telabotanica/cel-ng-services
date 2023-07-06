<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Entity\Occurrence;
use App\Model\PlantnetOccurrence;
use App\Model\PlantnetOccurrences;
use App\Service\AnnuaireService;
use App\Service\PlantnetPaginator;
use App\Service\PlantnetService;
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
    private $plantnetService;
    private $newJobsCount = 0;
    private $existingJobsCount = 0;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        AnnuaireService $annuaireService,
        PlantnetPaginator $plantnetPaginator,
        PlantnetService $plantnetService
    ) {
        $this->em = $em;
        $this->occurrenceRepository = $this->em->getRepository(Occurrence::class);
        $this->changeLogRepository = $this->em->getRepository(ChangeLog::class);
        $this->annuaireService = $annuaireService;
        $this->plantnetPaginator = $plantnetPaginator;
        $this->plantnetService = $plantnetService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates sync jobs of PlantNet occurrences created by Telabotaniste')
            ->addOption('resume', null, InputOption::VALUE_NONE,
                'If set, use last job updatedAt date to resume (overload from option)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                'If set, don’t persist changes to database')
            ->addOption('from', null, InputOption::VALUE_REQUIRED,
                'Get occurrences history from this timestamp')
			->addOption('to', null, InputOption::VALUE_REQUIRED,
                'Get occurrences history up to this timestamp')
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
        $endDate = (int) $input->getOption('to');
        $resume = $input->getOption('resume');
		
        if ($resume) {
            $startDate = $this->getLastJobUpdatedAt();
			
			$dateResumeStart = new \DateTime("now");
			$dateResumeStartS = $dateResumeStart->format('U');
			$endDate = $dateResumeStartS * 1000;
        }
		
        $email = (string) $input->getOption('email');

		$this->plantnetPaginator->start($startDate, $email, $endDate);

        do {
            $occurrences = $this->plantnetPaginator->getContent();
            if (!$occurrences) {
                break;
            }

            // Switch from PlantnetOccurrences to PlantnetOccurrence[]
            $occurrences = $occurrences->getData();
            foreach ($occurrences as $occurrence) {
                // filter out partners occurrences && obs without images
				/*
				if ($occurrence->getPartner() && $occurrence->getPartner()->getId() == 'tela'){
					print_r($occurrence->getId());
				}
				*/
                if ($occurrence->getPartner()) {
                    continue;
                }

				/* TODO Vérifier la date updated (format différent entre plantnet et tela)
				// TODO: Voir comment récupérer les maj d'obs tela faites sur PN (quelles infos récupérer?)
				// TODO: récupérer le PN id de l'obs tela ? -> update ou non?
				
                if ($occurrence->getPartner()) {
					if ($occurrence->getPartner()->getId() == 'tela'){
						$telaOccurrence = $this->occurrenceRepository->findOneBy(['id' => $occurrence->getPartner()
							->getObservationId()]);
						
						// Si l'occurrence n'a pas été update sur Plantnet on ne fait rien
						if ($occurrence->getDateUpdated() <= $telaOccurrence->getDateUpdated()){
							continue;
						}
					} else {
						continue;
					}
                }
				*/
				
				// TODO Vérifier si licence libre
				// TODO si obs tela -> rajouter $existingOccurrenceTela et update PlantNetId
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
		$now = new \DateTime("now");
		$timeFinished = $now->format('d-m-Y H:i:s');
		
            $this->io->success(sprintf(
                'Success! Got %d new jobs, %d already know, out of %d total processed occurrences! Job finished at %s',
                $this->newJobsCount, $this->existingJobsCount, count($occurrences), $timeFinished
            ));

            $this->io->comment(sprintf('Elapsed time: %.2f m / Consumed memory: %.2f MB', ($event->getDuration())/60000, $event->getMemory() / (1024 ** 2)));

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
			$changelog->setActionType($type);
			$this->em->persist($changelog);
            $this->existingJobsCount++;
			if (0 === $this->existingJobsCount % 50) {
				$this->em->flush();
			}
        }
    }

    private function getLastJobUpdatedAt(): int
    {
		$lastDate = 0;
        $changelog = $this->changeLogRepository->findOneBy(['entityName' => 'plantnet'], ['id' => 'desc']);
		// Si le process n'a pas été terminé (items encore présents dans le changelog) on récupère la dernière date
        if ($changelog) {
            $pnOccurrence = $this->plantnetService->getOccurrenceById($changelog->getEntityId());
            if ($pnOccurrence && $pnOccurrence->getDateUpdated()) {
				$lastDateSeconds = $pnOccurrence->getDateUpdated()->format('U');
				$lastDate = $lastDateSeconds * 1000;
            }
        } else { // Si le dernier process job s'est terminé on récupère la date de la dernière occurrence Plantnet
			// mise à jour
			$lastOccurrence = $this->occurrenceRepository->findOneBy(['inputSource' => 'PlantNet'], ['dateUpdated' =>
				'desc']);
			$lastDateSeconds = $lastOccurrence->getDateUpdated()->format('U');
			$lastDate = $lastDateSeconds * 1000;
		}

        return $lastDate;
    }
}
