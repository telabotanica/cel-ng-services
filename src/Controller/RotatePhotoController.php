<?php

namespace App\Controller;

use App\Entity\PhotoTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** 
 * Simple proxy to pl@]ntNet API.
 */
class RotatePhotoController extends AbstractController {
 
    private const PLANTNET_API_KEY = "2a10O8sbWystFClXLBjAJl6x0O";
    private const PLANTNET_API_URL = "https://my-api.plantnet.org/v1/identify/all";

    /**
     *
     * @Route("/api/photo/rotate", name="api_rotate_photo")
     */
    public function invoke(Request $request) {
        $photo = null;
        $degrees = 90;
        try {
            $this->rotatePhoto($photo, $degrees);
            // Let's return a RESTish payload for this rotation "resource":
            $jsonResp = array('id' => $t.getTimeStamp(), 'status' => "done" , 'status' => "done");
            return new Response(json_encode($jsonResp), Response::HTTP_OK, []);
        } catch (\Throwable $t) {
            $jsonResp = array('errorMessage' => $t.getMessage());
            return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }   
        exit;
    }

    private function rotatePhoto($photo, $degrees) {
        // Load the image
        $source = imagecreatefromjpeg($photo->getFilename());
        // Rotate
        $rotate = imagerotate($source, $degrees, 0);
        //and save it on your server...
        imagejpeg($rotate, "myNEWimage.jpg");
        imagedestroy($source);
    }

}
