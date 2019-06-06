<?php

namespace App\Controller;

use App\Entity\PhotoTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PhotoTagTreeApiController extends Controller {
 
    /**
     *
     * @Route("/api/photoTagTrees", name="api_photo_tag_trees")
     */
    public function getTree() {
 
        $user = $this->getUser();
    
        $tree = $this->getDoctrine()
        	->getRepository(PhotoTag::class)
        	->getTagTree($user->getId());
 
        return new JsonResponse($tree);
    }

}
