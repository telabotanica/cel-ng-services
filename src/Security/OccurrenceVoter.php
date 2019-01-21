<?php

// src/Security/OccurrenceVoter.php
namespace App\Security;

use App\Entity\Occurrence;
use App\Security\User\TelaBotanicaUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OccurrenceVoter extends Voter
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
        $occ = $subject;

        if ($user->isTelaBotanicaAdmin()) {
            return true;
        }
	if (null !== $occ->getProject()) {
		if ($user->getAdministeredProjectId() == $occ->getProject()->getId()) {
		    return $occ->getIsPublic();
		}

	}
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($occ, $user);
            case self::EDIT:
                return $this->canEdit($occ, $user);
            case self::DELETE:
                return $this->canDelete($occ, $user);
        }

        throw new \LogicException('Unknown attribute: ' . $attribute);
    }

    private function canView(Occurrence $occ, TelaBotanicaUser $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($occ, $user)) {
            return true;
        }

        return $occ->getIsPublic();
    }

    private function canEdit(Occurrence $occ, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $occ->getUserId() );
    }

    private function canDelete(Occurrence $occ, TelaBotanicaUser $user)
    {
        return ( $user->getId() === $occ->getUserId() );
    }

}
