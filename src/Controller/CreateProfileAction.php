<?php

namespace App\Controller;

use App\Entity\UserProfileCel;

use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateProfileAction {

    /**
     * Returns a new <code>CreatePhotoAction</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param EntityManagerInterface $doctrine The injected
     *        <code>EntityManagerInterface</code> service.
     */
    public function __construct(
        EntityManagerInterface $doctrine,
        Security $security) {

        $this->security = $security;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request): ?UserProfileCel
    {


        $profile = new UserProfileCel();
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
            $id = $parametersAsArray['userId'];

            $userId = $this->security->getToken()->getUser()->getId();
            
            if ( null !== $id ) {
                if ( $userId == $id ) {    
                    $profile->setUserId($id);
                    $this->doctrine->persist($profile);
                    $this->doctrine->flush();
    
                    return $profile;
                }
                else {
    return null;
    //            throw new UnloggedAccessException('You must be logged into tela-botanica SSO system to access this part of the app.');
                }
            }
            else {
    return null;
                //new Malformed Error
            }   



        }
    }
}

