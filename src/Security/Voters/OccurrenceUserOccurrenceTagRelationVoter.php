<?php

namespace App\Security\Voters;

use App\Entity\OccurrenceUserOccurrenceTagRelation;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * <code>AbstractVoter</code> for 
 * <code>OccurrenceUserOccurrenceTagRelation</code> resources/entities.
 *
 * @package App\Security\Voters
 */
class OccurrenceUserOccurrenceTagRelationVoter extends AbstractVoter
{

    /**
     * @inheritdoc
     */
    protected function supportsEntity($subject): bool {

        if (!$subject instanceof OccurrenceUserOccurrenceTagRelation) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute(
        $attribute, $subject, TokenInterface $token) {

        $user = $token->getUser();

        // Only the owner can view/update/delete this resource:        
        return ( 
            ( $user->getId() === $subject->getOccurrence()->getUserId() ) && 
            ( $user->getId() === $subject->getUserOccurrenceTag()->getUserId() ) );
    }


}
