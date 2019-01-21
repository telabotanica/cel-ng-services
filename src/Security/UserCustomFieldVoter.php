<?php

namespace App\Security;

use App\Entity\UserCustomField;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

// @refactor: DRY this and promote to superclass
class UserCustomFieldVoter extends Voter
{

    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports($attribute, $subject)
    {

        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof UserCustomField) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();
        /** @var UserCustomField $profile */
        $inst = $subject;

        // Only the owner can view/update/delete this resource:        
        return ( $user->getId() === $inst->getUserId() );
    }


}
