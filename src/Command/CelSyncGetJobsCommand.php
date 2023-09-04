<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Entity\Occurrence;
use App\Model\PlantnetOccurrence;
use App\Model\PlantnetOccurrences;
use App\Service\AnnuaireService;
use App\Service\OccurrenceBuilderService;
use App\Service\PhotoBuilderService;
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
	
	private $occurrenceBuilderService;
	private $photoBuilderService;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        AnnuaireService $annuaireService,
        PlantnetPaginator $plantnetPaginator,
        PlantnetService $plantnetService,
		OccurrenceBuilderService $occurrenceBuilderService,
		PhotoBuilderService $photoBuilderService
    ) {
        $this->em = $em;
        $this->occurrenceRepository = $this->em->getRepository(Occurrence::class);
        $this->changeLogRepository = $this->em->getRepository(ChangeLog::class);
        $this->annuaireService = $annuaireService;
        $this->plantnetPaginator = $plantnetPaginator;
        $this->plantnetService = $plantnetService;
		$this->occurrenceBuilderService = $occurrenceBuilderService;
		$this->photoBuilderService = $photoBuilderService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates sync jobs of PlantNet occurrences created by Telabotaniste')
            ->addOption('resume', null, InputOption::VALUE_NONE,
                'If set, use last job updatedAt date to resume (overload from option)')
			->addOption('yesterday', null, InputOption::VALUE_NONE,
                'If set, get jobs from yesterday')
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
        $yesterday = $input->getOption('yesterday');
		
		$today = new \DateTime("now");

		$timeStarted = $today->format('d-m-Y H:i:s');
		$this->io->comment(sprintf('script started at %s .', ($timeStarted)));
		
		if ($resume) {
            $startDate = $this->getLastJobUpdatedAt();
			$endDate = $today->getTimestamp() * 1000;
        }
		
		if ($yesterday){
			// Calculer la date de la journée précédente
			$datePrecedente = (clone $today)->modify('-1 day');
			
			// Définir la startDate au début de la journée précédente (00:00:00)
			$startDateYesterday = $datePrecedente->setTime(0, 0, 0);
			
			// Définir la endDate à la fin de la journée précédente (23:59:59)
			$endDateYesterday = (clone $datePrecedente)->setTime(23, 59, 59);
			
			// Convertir les dates en millisecondes
			$startDate = $startDateYesterday->getTimestamp() * 1000;
			$endDate = $endDateYesterday->getTimestamp() * 1000;
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
                // filter out occurrences
                if (
					$occurrence->getPartner() ||
//					!$occurrence->isValid() ||
					$occurrence->getCurrentName() == '' ||
					(count($occurrence->getImages()) == 1 && $occurrence->getImages()[0]->getQualityVotes()->getMinus() > 0) ||
					!$occurrence->getLicense()
				) {
                    continue;
                }

				// On vérifie si l'obs vient d'un telabotaniste sinon on dégage
				if ($this->annuaireService->isKnownUser($occurrence->getAuthor()->getEmail())){
					
				// TODO quoi updater ? (on ne veux pas réinitialisé  le nom / nom Id et référentiel) -> job spécifique ?
					// Si l'obs vient d'un partner autre que tela on zappe
					/*
					if ($occurrence->getPartner()) {
						$needUpdate = $this->checkUpdatedRemote($occurrence);
						if (!$needUpdate){
							continue;
						}
					}
					*/
					$existingOccurrence = $this->occurrenceRepository->findOneBy(['plantnetId' => $occurrence->getId()]);
					//TODO vérifier si l'update c'est juste le score/vote (on s'en balek du vote PN) donc on update que si le nom, les images ou la localisation a changée
					if ($existingOccurrence) {
						if ($occurrence->isDeleted() || $occurrence->isCensored()) {
							$this->addJob('delete', $occurrence->getId());
						} else {
							$previousSciNameId = $existingOccurrence->getAcceptedSciNameId();
							$newSciNameId = $this->occurrenceBuilderService->getPnTaxon($occurrence)[1]['acceptedSciNameId'];
							$imageChanged = $this->photoBuilderService->isImagesChanged($existingOccurrence, $occurrence);
							$geoChanged = $this->occurrenceBuilderService->isGeoChanged($existingOccurrence, $occurrence);
							
							if (
								$previousSciNameId != $newSciNameId ||
								$imageChanged ||
								$geoChanged
							){
								$this->addJob('update', $occurrence->getId());
							}
						}
					} else {
						$this->addJob('create', $occurrence->getId());
					}
				}
            }
			// On push en bdd à chaque page pour pouvoir resume + facilement en cas de crash
			if (!$dryRun) {
				$this->em->flush();
			}
        } while ($this->plantnetPaginator->nextPage());

//        if (!$dryRun) {
//            $this->em->flush();
//        }

        $event = $stopwatch->stop('pn-sync-get-jobs');
		$end = new \DateTime("now");
		$timeFinished = $end->format('d-m-Y H:i:s');
		
            $this->io->success(sprintf(
                'Success! Got %d new jobs, %d already know, out of %d total processed occurrences! Job started at %s, Job finished at %s',
                $this->newJobsCount, $this->existingJobsCount, count($occurrences), $timeStarted, $timeFinished
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
	
	private function checkUpdatedRemote(PlantnetOccurrence $occurrence){
		if ($occurrence->getPartner()->getId() == 'tela') {
			$telaOccurrence = $this->occurrenceRepository->findOneBy(['id' => $occurrence->getPartner()->getObservationId()]);
			
			// Si l'occurrence n'a pas été update sur Plantnet on ne fait rien
			if (!$telaOccurrence || ($occurrence->getDateUpdated() <= $telaOccurrence->getDateUpdated())){
				return false;
			}
			return true;
		} else {
			return false;
		}
	}
}
