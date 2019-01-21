<?php

namespace App\Controller;

use App\Form\OccurrenceType;
use App\Utils\FromArrayOccurrenceCreator;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Vich\UploaderBundle\Storage\StorageInterface;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 */
final class ServeZippedPhotosAction
{
    private $validator;
    private $doctrine;
    private $factory;
    private $tokenStorage;

    public function __construct(StorageInterface $storage, TokenStorageInterface $tokenStorage, RegistryInterface $doctrine)
    {
    	$this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
        $this->storage = $storage;
    }


    public function __invoke(Request $request): Response
    {
        $zip = new \ZipArchive;
        $zipName = 'PhotosCel_'.time().".zip";

        // @todo trycatch 500
        if ($zip->open("/tmp/" . $zipName,  \ZipArchive::CREATE))
        {
            $em = $this->doctrine->getManager();
		    $occRepo = $em->getRepository('App\Entity\Photo');

            // @todo Do a DQL 'in' query instead:
            $ids = $request->query->get('id');
            $photos = $occRepo->findById($ids);
                 
            foreach ($ids as $id) {
                $photos = $occRepo->findById($id);
                if (sizeof($photos)>0) {
                    $photo = $photos[0];
                    $filePath = $this->storage->resolvePath($photo, 'file');
                    $zip->addFile($filePath, $photo->getOriginalName());
                }
            }

            $zip->close();

            $response = new Response(file_get_contents("/tmp/" . $zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
            $response->headers->set('Content-length', filesize("/tmp/" . $zipName));

            return $response;

        }
        else {
            $response = new Response();
            $response->setStatusCode(500);
            return $response;
        }
    }

}


