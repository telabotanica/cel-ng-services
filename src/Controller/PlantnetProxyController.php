<?php

namespace App\Controller;

use App\Entity\PhotoTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** 
 * Simple proxy to pl@]ntNet API.
 */
class PlantnetProxyController extends AbstractController {
 
    private $plantnetApiKey = "2a10O8sbWystFClXLBjAJl6x0O";
    private $plantNetApiUrl = "https://my-api.plantnet.org/v1/identify/all";

    /**
     *
     * @Route("/api/plantnet", name="api_plantnet")
     */
    public function invoke(Request $request) {
        $url = $this->buildUrl($request);
        $fp = fopen($url, 'rb');
        fpassthru($fp);
        exit;
    }

    private function buildUrl($request) {
        return $this->plantNetApiUrl . '?' . urldecode($request->getQueryString()) . '&api-key=' . $this->plantnetApiKey; 
    }

}
