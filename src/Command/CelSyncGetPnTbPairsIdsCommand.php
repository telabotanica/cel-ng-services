<?php

namespace App\Command;

use App\Entity\Occurrence;
use App\Entity\PnTbPair;
use App\Service\PlantnetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class CelSyncGetPnTbPairsIdsCommand extends Command
{
    protected static $defaultName = 'cel:sync:get-pn-tb-pairs-ids';

    private $em;
    private $plantnetService;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, PlantnetService $plantnetService)
    {
        $this->em = $em;
        $this->plantnetService = $plantnetService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get all PlantNet Occurrences corresponding IDs to Tela Botanica Occurrences')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'If set, don’t persist changes to database')
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
        $stopwatch->start('pn-tb-pairs-ids');

        $dryRun = $input->getOption('dry-run');

        $occurrenceRepository = $this->em->getRepository(Occurrence::class);
        $pnTbPairRepository = $this->em->getRepository(PnTbPair::class);

        $this->io->text('Wait for it...');
        $pairs = $this->plantnetService->getExistingPairs();

        $bar = new ProgressBar($output, count($pairs));

        $this->io->text('Let’s go computing all that stuff!');
        $unknownOccurrences = 0;
        $knownOccurrences = 0;
        $savedPairs = 0;
        foreach ($pairs as $pair) {
            $bar->advance();

            $occurrence = $occurrenceRepository->findOneBy(['id' => $pair['tela']]);
            if ($occurrence) {
                $pnTbPair = $pnTbPairRepository->findOneBy(['occurrence' => $occurrence]);
                if (!$pnTbPair) {
                    $pnTbPair = new PnTbPair(
                        $occurrence,
                        $pair['id'],
                        new \DateTime(),
                    );
                    $this->em->persist($pnTbPair);
                    $savedPairs++;
                } else {
                    $knownOccurrences++;
                }
            } else {
                $unknownOccurrences++;
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }
        $bar->finish();

        $event = $stopwatch->stop('pn-tb-pairs-ids');
        $this->io->success(
            sprintf('PN TB pairs successfully synced! (Stats: %s new pairs | %s known occurrences | %s unknown)',
                $savedPairs, $knownOccurrences, $unknownOccurrences)
        );
        $this->io->comment(sprintf('Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));

        return 0;
    }
}
