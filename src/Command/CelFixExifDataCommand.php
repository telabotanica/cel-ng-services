<?php

namespace App\Command;

use App\Repository\PhotoRepository;
use App\Utils\ExifExtractionUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CelFixExifDataCommand extends Command
{
    private $em;
    private $photoRepository;

    protected static $defaultName = 'cel:fix:exif-data';

    public function __construct(
        EntityManagerInterface $em,
        PhotoRepository $photoRepository
    ) {
        $this->em = $em;
        $this->photoRepository = $photoRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Scan photos in order to fix stored data extracted from exif')
            ->addArgument('photo-id', InputArgument::OPTIONAL, 'ID de l’entité de la photo à scanner (eg: 2517181)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'If set, don’t persist changes to database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $photoId = $input->getArgument('photo-id');

        $modifiedCount = 0;

        $photos = [];
        if ($photoId) {
            $io->note(sprintf('Single photo selected: %s', $photoId));

            $photos[] = $this->photoRepository->findOneBy(['id' => $photoId]);
        } else {
            $photos = $this->photoRepository->findAll();
        }

        foreach ($photos as $photo) {
            $exifUtils = new ExifExtractionUtils($photo->getContentUrl());
            $dateShot = $exifUtils->getShootingDate();
            if ($dateShot && $dateShot != $photo->getDateShot()) {
                if ($output->isVerbose()) {
                    $io->comment(sprintf('Changed photo #%s from %s to %s',
                        $photo->getId(),
                        $photo->getDateShot()->format('c') ?? 'null',
                        $dateShot->format('c')
                    ));
                }

                $photo->setDateShot($dateShot);
                $modifiedCount++;
            }
        }

        if (!$input->getOption('dry-run')) {
            $this->em->flush();
        }

        $io->success('Yeah! '.$modifiedCount.' photos modified \o/');
    }
}
