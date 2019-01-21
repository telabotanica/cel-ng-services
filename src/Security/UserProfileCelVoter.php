<?php

// src/Security/CelUserProfileVoter.php
namespace App\Security;

use App\Entity\UserProfileCel;
use App\Security\User\TelaBotanicaUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserProfileCelVoter extends Voter
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

        if (!$subject instanceof UserProfileCel) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();
        /** @var CelUserProfile $profile */
        $profile = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($profile, $user);
            case self::EDIT:
                return $this->canEdit($profile, $user);
            case self::DELETE:
                return $this->canDelete($profile, $user);
        }

        throw new \LogicException('Unknown attribute: ' . $attribute);
    }

    private function canView(UserProfileCel $profile, User $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

    private function canEdit(UserProfileCel $profile, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

    private function canDelete(UserProfileCel $profile, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

}
