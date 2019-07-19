<?php

namespace App\Controller;

use App\Entity\PhotoTag;
use App\Repository\PhotoRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;

/** 
 * Rotates the image behind a <code>Photo</code>.
 */
class RotatePhotoController extends AbstractController {
 
    // Symfony services
    private $doctrine;
    // the <code>Security</code> service to retrieve the current user:
    protected $security;

    const DUPLICATE_NAME_MSG = "A photo with the same name is already present "
        . "in the user gallery. This is not allowed.";

    /**
     * Returns a new <code>RotatePhotoController</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param RegistryInterface $doctrine The injected 
     *        <code>RegistryInterface</code> service.
     * @param Security $security The injected <code>Security</code> service.
     *
     * @return CreatePhotoAction Returns a new  
     *         <code>CreatePhotoAction</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        RegistryInterface $doctrine, 
        Security $security) {
        $this->security = $security;
        $this->doctrine = $doctrine;
    }


    /**
     *
     * @Route("/api/photo_rotations", name="api_rotate_photo")
     */
    public function invoke(Request $request) {

        $id = $request->query->get('photoId');
        // @refactor: create a transient ImageRotation entity + auto hydrate using symfoy forms instead
        $photo = null;
        $photoRepo = $this->doctrine->getRepository('App\Entity\Photo');
        $photo = $photoRepo->find($id);
        $degrees = $request->query->get('degrees') ? $request->query->get('degrees') : 90;

        try {
            $this->rotatePhoto($photo, $degrees);
            // Let's return a RESTish payload for this imageRotation "resource":
            $jsonResp = array('id' => time(), 'photoId' => $id, 'status' => "done", 'degrees' => $degrees);

            return new Response(json_encode($jsonResp), Response::HTTP_OK, []);
        } catch (\Throwable $t) {
            $jsonResp = array('errorMessage' => $t.getMessage());
            return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }   
        exit;
    }

    private function rotatePhoto($photo, $degrees) {
        $source = $this->loadImage($photo);
        // Rotate the image:
        $rotate = imagerotate($source, $degrees, 0);
        this.saveImage($rotate, $photo->getContentUrl());
    }

    private function loadImage($photo) {
        // Load the image
        if ( $photo->getMimeType() == 'image/jpeg' ) {
            return imagecreatefromjpeg($photo->getContentUrl());
        }
        else if ( $photo->getMimeType() == 'image/png' ) {
            return imagecreatefrompng($photo->getContentUrl());
        }
        throw new \Exception('The image is neither a jpeg nor a png.');        
    }

    private function saveImage($photo) {
        // Load the image
        if ( $photo->getMimeType() == 'image/jpeg' ) {
            return imagejpeg($photo);
        }
        else if ( $photo->getMimeType() == 'image/png' ) {
            imagesavealpha($photo, true);
            return imagepng($photo);
        }
        throw new \Exception('The image is neither a jpeg nor a png.');        
    }

}
