<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\UserOccurrenceTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class UserOccurrenceTagTreeApiController extends Controller
{


    /**
     *
     * @Route("/api/userOccurrenceTagTrees", name="api_user_occurrence_tag_trees")
     */
    public function getTree()
    {
 
        $user = $this->getUser();
    
        $tree = $this->getDoctrine()
        ->getRepository(UserOccurrenceTag::class)
        ->getTagTree($user->getId());

 
        return new JsonResponse($tree );
    }
}
