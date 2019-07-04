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
class PlantnetProxyController extends AbstractController {
 
    /**
     *
     * @Route("/api/plantnet", name="api_plantnet")
     */
    public function invoke(Request $request) {
        $url = $this->buildUrl($request);
        
        try {
            $fp = fopen($url, 'rb');
            fpassthru($fp);
        } catch (\Throwable $t) {
            if (strpos($t->getMessage(), '404 Not Found') !== false) {
                return new Response('Species not found.', Response::HTTP_NOT_FOUND, []);
            }
            else {
var_dump($t);
                $jsonResp = array('errorMessage' => $t.getMessage());
                return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);
            }
        }   
        exit;
    }

    private function buildUrl($request) {
        return getenv('PLANTNET_API_URL') . '?' . urldecode($request->getQueryString()) . '&api-key=' . getenv('PLANTNET_API_KEY'); 
    }

}
