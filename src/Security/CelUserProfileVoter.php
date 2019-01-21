<?php

// src/Security/CelUserProfileVoter.php
namespace App\Security;

use App\Entity\CelUserProfile;
use App\Security\User\TelaBotanicaUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CelUserProfileVoter extends Voter
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

        if (!$subject instanceof Occurrence) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();
        /** @var Occurrence $occ */
        $profile = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($profile, $user);
            case self::EDIT:
                return $this->canEdit($profile, $user);
            case self::DELETE:
                return $this->canDelete($profile, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(CelUserProfile $profile, User $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

    private function canEdit(CelUserProfile $profile, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

    private function canDelete(CelUserProfile $profile, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $profile->getUserId() );
    }

}
