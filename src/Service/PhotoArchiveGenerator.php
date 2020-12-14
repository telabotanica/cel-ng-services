<?php

namespace App\Service;

use App\Exception\ZipOpeningException;
use App\Repository\PhotoRepository;

use ZipArchive;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Vich\UploaderBundle\Storage\StorageInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Generates a zip archive containing the photos based on their ids. 
 *
 * @package App\Service
 */
final class PhotoArchiveGenerator {

    private $doctrine;
    private $storage;
    private $tokenStorage;

    const OPENING_FILE_ERROR_MSG = 'An error occurred opening the '
        . 'photo archive zip file';

    /**
     * Returns a new <code>PhotoArchiveGenerator</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param TokenStorageInterface $tokenStorage The injected 
     *        <code>TokenStorageInterface</code> service.
     * @param StorageInterface $storage The injected 
     *        <code>StorageInterface</code> service.
     * @param EntityManagerInterface $doctrine The injected
     *        <code>EntityManagerInterface</code> service.
     * 
     * @return PhotoArchiveGenerator Returns a new  
     *         <code>PhotoArchiveGenerator</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        StorageInterface $storage, 
        EntityManagerInterface $doctrine,
        TokenStorageInterface $tokenStorage) {

    	$this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
        $this->storage = $storage;
    }

    /**
     * Generates and returns a <code>ZipArchive</code> containing the photos 
     * with provided ids and located at provided file path.
     *
     * @param Array $ids the IDs of the photo to add to the archive.
     * @param string $zipFilePath the path to the zip file.
     * 
     * @return a <code>ZipArchive</code> containing the photos 
     *         with provided ids and located at provided file path.
     */
    public function generate($ids, string $zipFilePath): ZipArchive {

        $zip = new \ZipArchive;

        // @todo trycatch 500
        if ($zip->open(
            $zipFilePath,  
            \ZipArchive::CREATE)) {

            $em = $this->doctrine;
		    $photoRepo = $em->getRepository('App\Entity\Photo');
            // @todo Do a DQL 'in' query instead:
            $photos = $photoRepo->findById($ids);
            // First populate the archive file:                 
            $this->populateZip($zip, $photoRepo, $ids); 
            $zip->close();
            
            // Then, send the generated file:
            return $zip;
        }
        else {
            throw new ZipOpeningException(
                PhotoArchiveGenerator::OPENING_FILE_ERROR_MSG);
        }
    }


    private function populateZip(ZipArchive $zip, PhotoRepository $repo, array $ids) {
        foreach ($ids as $id) {
            $photos = $repo->findById($id);
            if (sizeof($photos)>0) {
                $photo = $photos[0];
                $zip->addFile($photo->getContentUrl(), basename($photo->getContentUrl()));
            }
        }
    }

}


