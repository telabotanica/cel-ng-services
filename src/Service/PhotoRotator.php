<?php

namespace App\Service;

use App\Entity\PhotoTag;
use App\Repository\PhotoRepository;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/** 
 * Rotates the image behind a <code>Photo</code>.
 */
class PhotoRotator {
 
    // Symfony services
    private $doctrine;
    // the <code>Security</code> service to retrieve the current user:
    protected $security;

    private $urlMiniregen;

    const DUPLICATE_NAME_MSG = "A photo with the same name is already present "
        . "in the user gallery. This is not allowed.";

    /**
     * Returns a new <code>RotatePhotoController</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param EntityManagerInterface $doctrine The injected
     *        <code>EntityManagerInterface</code> service.
     * @param Security $security The injected <code>Security</code> service.
     *
     * @return CreatePhotoAction Returns a new  
     *         <code>CreatePhotoAction</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        EntityManagerInterface $doctrine,
        Security $security,
        string $urlMiniregen
    ) {
        $this->security = $security;
        $this->doctrine = $doctrine;
        $this->urlMiniregen = $urlMiniregen;
    }



    public function rotatePhotoById(int $photoId, int $degrees) {

        $photo = null;

        $photoRepo = $this->doctrine->getRepository('App\Entity\Photo');
        $photo = $photoRepo->find($photoId);

        $mimetype = $photo->getMimeType();

        $imgs = $this->loadImages($photo);

       foreach ($imgs as $path => $img) {  
            // Rotate the image:
            $rotate = imagerotate($img, $degrees, 0);
            $this->saveImage($rotate, $mimetype, $path);

        }

        // Call to mini-regen service to generate new thumbnails
        $miniregenServiceUrl = sprintf($this->urlMiniregen, $photo->getId());
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
