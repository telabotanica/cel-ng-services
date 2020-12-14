<?php

namespace App\Controller;

use App\Entity\PhotoTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;

/** 
 * Simple proxy to pl@]ntNet API.
 */
class PlantnetProxyController extends AbstractController {
 
    const PN_RESPONSE_PREFIX = 'PlantNetResponse';
    const PN_RESPONSE_EXTENSION = ".json";

    private $tmpFolder;

    public function __construct(string $tmpFolder) {
        $this->tmpFolder = $tmpFolder;
    }

    /**
     *
     * @Route("/api/plantnet", name="api_plantnet")
     */
    public function invoke(Request $request, string $tmpFolder, string $plantnetApiUrl, string $plantnetApiKey) {

        $url = $this->buildUrl($request, $plantnetApiUrl, $plantnetApiKey);

        $pnRespFileName = PlantnetProxyController::PN_RESPONSE_PREFIX . time();
        $pnRespFileName .= PlantnetProxyController::PN_RESPONSE_EXTENSION;
        $pnRespFilePath = $tmpFolder . '/' . $pnRespFileName;

        try {

            file_put_contents($pnRespFilePath, fopen($url, 'r'));
            // Now send the generated file:
            $response = new Response(file_get_contents($pnRespFilePath));
            return $response;

        } catch (\Exception $t) {

            // Translate the error message raised by the proxied service: 
            $jsonResp = array('errorMessage' => $t->getMessage());
            // Return a  500 with an informative msg as JSON:
            return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }
    }


    private function buildUrl(Request $request, string $plantnetApiUrl, string $plantnetApiKey) {
        return $plantnetApiUrl . '?' . urldecode($request->getQueryString()) . '&api-key=' . $plantnetApiKey;
    }

}
