<?php

namespace App\Controller;

use App\Service\PhotoArchiveGenerator;
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
 * Returns a zip archive containing the photos which ids have been passed as
 * parameters. 
 *
 * @package App\Controller
 */
final class ServeZippedPhotosAction {

    private $photoArchiveGenerator;
    private $tmpFolder;

    const ENTITY_FILE_PROPERTY_NAME = 'file';
    const ZIP_FILE_PREFIX = 'PhotosCel_';
    const ZIP_EXTENSION = ".zip";

    /**
     * Returns a new <code>ServeZippedPhotosAction</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param PhotoArchiveGenerator $photoArchiveGenerator The service 
     *        responsible for generating the photos.
     * 
     * @return ServeZippedPhotosAction Returns a new  
     *         <code>ServeZippedPhotosAction</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        PhotoArchiveGenerator $photoArchiveGenerator, string $tmpFolder) {

    	$this->photoArchiveGenerator = $photoArchiveGenerator;
        $this->tmpFolder = $tmpFolder;
    }

    /**
     * Invokes the controller/action.
     *
     * @param Request $request The HTTP <code>Request</code> issued 
     *        by the client.
     * 
     * @return Response Returns an HTTP <code>Response</code> reflecting
     *         the action result.
     */
    public function __invoke(Request $request): Response {

        $zipName = ServeZippedPhotosAction::ZIP_FILE_PREFIX . time();
        $zipName .= ServeZippedPhotosAction::ZIP_EXTENSION;
        $zipFilePath = $this->tmpFolder . '/' . $zipName;
        $ids = $request->query->get('id');
        $this->photoArchiveGenerator->generate($ids, $zipFilePath);

        // Then, send the generated file:
        return $this->buildResponse($zipFilePath, $zipName);
    }


    private function buildResponse(string $zipFilePath, string $zipName): Response {
        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set(
            'Content-Disposition', 
            'attachment;filename="' . $zipName . '"');
        $response->headers->set(
            'Content-length', 
            filesize($zipFilePath));

        return $response;
    }

}



