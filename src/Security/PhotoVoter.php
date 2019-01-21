<?php

// src/Security/PhotoVoter.php
namespace App\Security;

use App\Entity\Photo;
use App\Security\User\TelaBotanicaUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PhotoVoter extends Voter
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

        if (!$subject instanceof Photo) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        /** @var Photo $photo */
        $photo = $subject;
/*
        if (!$user instanceof TelaBotanicaUser) {
            // the user must be logged in; if not, deny access
            return false;
        }
*/
        if ($user->isTelaBotanicaAdmin()) {
            return true;
        }
        if ( $photo->getOccurrence() !== null ) {
            if ($user->getAdministeredProjectId() == $photo->getOccurrence()->getProject()->getId()) {
                return $photo->getIsPublic();
            }
        }        
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($photo, $user);
            case self::EDIT:
                return $this->canEdit($photo, $user);
            case self::DELETE:
                return $this->canDelete($photo, $user);
        }

        throw new \LogicException('Unknown attribute: ' . $attribute);
    }

    private function canView(Photo $photo, TelaBotanicaUser $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($photo, $user)) {
            return true;
        }

        return $photo->getIsPublic();
    }

    private function canEdit(Photo $photo, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $photo->getUserId() );
    }

    private function canDelete(Photo $photo, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $photo->getUserId() );
    }

}
