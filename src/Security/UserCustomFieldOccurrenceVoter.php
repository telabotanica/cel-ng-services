<?php

// src/Security/CelUserProfileVoter.php
namespace App\Security;

use App\Entity\UserProfileCel;
use App\Security\User\TelaBotanicaUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

//@refactor dry this
class UserCustomFieldOccurrenceVoter extends Voter
{

    protected const VIEW = 'view';
    protected const EDIT = 'edit';
    protected const DELETE = 'delete';

    protected function supports($attribute, $subject)
    {

        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof UserCustomFieldOccurrence) {
            return false;
        }

        return true;
    }



    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();
        /** @var UserCustomFieldOccurrence $inst */
        $inst = $subject;

        // Only the owner can view/update/delete this resource:        
        return ( $user->getId() === $inst->getUserId() );
    }


}
