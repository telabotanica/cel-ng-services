<?php

namespace App\Command;

use App\Entity\Occurrence;
use App\Entity\PnTbPair;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

class CelSyncGetPnTbPairsIdsCommand extends Command
{
    protected static $defaultName = 'cel:sync:get-pn-tb-pairs-ids';

    private $em;
    private $pnTbPairRepository;
    private $occurrenceRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->pnTbPairRepository = $this->em->getRepository(PnTbPair::class);
        $this->occurrenceRepository = $this->em->getRepository(Occurrence::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get all PlantNet Occurrences corresponding IDs to Tela Botanica Occurrences')
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'If set, don’t persist changes to database')
            ->addArgument('pn-token', InputArgument::REQUIRED, 'PN Token')
//            ->addOption('pn-token', null, InputOption::VALUE_REQUIRED, 'PN Token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $client = HttpClient::create();

        $dryRun = $input->getOption('dry-run');
        $pnToken = $input->getArgument('pn-token');

        // get IDS
        $io->text('Getting info from PN server.');
        $response = $client->request('GET', 'https://my-api.plantnet.org/v2/observations/partnerids?api-key='.$pnToken, [
            'headers' => [
                'Accept' => 'text/plain',
            ],
        ]);
        $io->text('Fetching response, could take a moment...');

        $pairs = $response->getContent();
        $pairs = json_decode($pairs, true);

        $bar = new ProgressBar($output, count($pairs));

        $io->text('Let’s go computing all that stuff!');
        $unknownOccurrences = 0;
        $knownOccurrences = 0;
        $savedPairs = 0;
        foreach ($pairs as $pair) {
            $bar->advance();

            $occurrence = $this->occurrenceRepository->findOneBy(['id' => $pair['tela']]);
            if ($occurrence) {
                $pnTbPair = $this->pnTbPairRepository->findOneBy(['occurrence' => $occurrence]);
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

        $io->newLine();
        $io->success(
            sprintf('PN TB pairs successfully synced! (Stats: %s new pairs | %s known | %s unknown)',
                $savedPairs, $knownOccurrences, $unknownOccurrences)
        );
    }
}
