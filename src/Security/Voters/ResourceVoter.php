<?php

namespace App\Security\Voters;

use App\Entity\Occurrence;
use App\Entity\Photo;
use App\Entity\OwnedEntitySimpleInterface;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * <code>AbstractVoter</code> for <code>Occurrence</code> and 
 * <code>Photo</code> resources/entities.
 *
 * @package App\Security\Voters
 */
class ResourceVoter extends AbstractVoter {

    /**
     * @inheritdoc
     */
    protected function supportsEntity($subject): bool {
        dump(!$subject instanceof Photo);
        dump(!$subject instanceof Occurrence);

        dump(!$subject instanceof Occurrence || !$subject instanceof Photo);
        if ($subject instanceof Occurrence) {
            return true;
        }

        if ($subject instanceof Photo) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute(
        $attribute, $subject, TokenInterface $token) {

        $user = $token->getUser();

        if ($user->isTelaBotanicaAdmin()) {
            return true;
        }
	    if (null !== $subject->getProject()) {
		    if ($user->getAdministeredProjectId() == $subject->getProject()->getId()) {
		        return $subject->getIsPublic();
		    }
	    }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    /**
     * @inheritdoc
     */
    protected function canView(
        OwnedEntitySimpleInterface $occ, TelaBotanicaUser $user): bool {

        // if they can edit, they can view
        if ($this->canEdit($occ, $user)) {
            return true;
        }

        return $occ->getIsPublic();
    }

}
