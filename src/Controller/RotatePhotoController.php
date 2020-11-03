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

}
