<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\PhotoTag;
use App\Repository\PhotoRepository;
use App\Service\PhotoRotator;

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

    const ERROR_MSG = "Impossible to rotate the images associated "
        . "to the photo.  An error occurred...";

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
        PhotoRotator $photoRotator) {
        $this->photoRotator = $photoRotator;
    }


    /**
     *
     * @Route("/api/photo_rotations", name="api_rotate_photo")
     */
    public function invoke(Request $request) {

        $photoId = $request->query->get('photoId');
        $degrees = $request->query->get('degrees') ? 
            $request->query->get('degrees') : 90;

        try {
            $this->photoRotator->rotatePhotoById($photoId, $degrees);
            // Let's return a RESTish payload for this imageRotation "resource":
            $jsonResp = array(
                'id' => time(), 
                'photoId' => $photoId, 
                'status' => "done", 
                'degrees' => $degrees);

            return new Response(json_encode($jsonResp), Response::HTTP_OK, []);
        } catch (\Throwable $t) {

            $jsonResp = array(
                'errorMessage' => RotatePhotoController::ERROR_MSG);
            return new Response(
                json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);

        }   
        exit;
    }

    private function rotatePhoto(Photo $photo, $degrees) {

        $mimetype = $photo->getMimeType();

        $imgs = $this->loadImages($photo);

       foreach ($imgs as $path => $img) {
           // Save original image
           if (!file_exists($path.'.orig')) {
               $this->saveImage($img, $mimetype, $path.'.orig');
           }
            // Rotate the image:
            $rotate = imagerotate($img, $degrees, 0);
            $this->saveImage($rotate, $mimetype, $path);
        }

        // Call to mini-regen service to generate new thumbnails
        $miniregenServiceUrl = sprintf(getenv('URL_MINIREGEN'), $photo->getId());
        $ch = curl_init($miniregenServiceUrl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception ('curl erreur: ' . curl_errno($ch));
        }
        curl_close($ch);
    }

    private function loadImages($photo) {

        $paths = $photo->getContentUrls();

        $imgs = [];

       foreach ($paths as $path) { 
            if ( file_exists($path) ) {
                // Load the image
                if ( $photo->getMimeType() == 'image/jpeg' ) {
                    $imgs[$path] = imagecreatefromjpeg($path);
                }
                else if ( $photo->getMimeType() == 'image/png' ) {
                    $imgs[$path] = imagecreatefrompng($path);
                }
                else {
                    throw new \Exception('The image is neither a jpeg nor a png.');
                }
            }
        } 
        
        return $imgs;
    }



    private function extractMimeType($photo) {
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


    private function saveImage($img, $mimetype, $path) {
        // Load the image
        if ( $mimetype == 'image/jpeg' ) {
            return imagejpeg($img, $path);
        }
        else if ( $mimetype == 'image/png' ) {
            imagesavealpha($img, true);
            return imagepng($img, $path);
        }
        throw new \Exception('The image is neither a jpeg nor a png.');        
    }
}
