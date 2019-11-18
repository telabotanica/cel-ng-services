<?php

namespace App\Security\Voters;

use App\Entity\OwnedEntitySimpleInterface;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Abstract security voter for entities/resources which need access control
 * (ACL) based on current user.
 *
 * This class provides basic behavior : only the owner or a TelaBotanica 
 * admin can create/read/update/delete the entity/resource instances.
 *
 * @package App\Security\Voters
 */
abstract class AbstractVoter extends Voter {

    const VIEW      = 'view';
    const EDIT      = 'edit';
    const DELETE    = 'delete';

    protected function supports($attribute, $subject) {
        return ( $this->supportsEntity($subject) && 
            $this->supportsAttribute($attribute) );
    }

    abstract protected function supportsEntity($subject): bool;

    protected function supportsAttribute($attribute): bool {
        $supportedAtts = array(self::VIEW, self::EDIT, self::DELETE);
        // if the attribute isn't one we support, return false
        if ( !in_array($attribute, $supportedAtts) ) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(
        $attribute, $subject, TokenInterface $token) {

        $user = $token->getUser();

        if ($user->isTelaBotanicaAdmin()) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    protected function canView(
        OwnedEntitySimpleInterface $entitity, TelaBotanicaUser $user): bool {
        return ( $user->getId() === $entitity->getUserId() );
    }

    protected function canEdit(
        OwnedEntitySimpleInterface $entitity, TelaBotanicaUser $user): bool {
        return ( $user->getId() === $entitity->getUserId() );
    }

    protected function canDelete(
        OwnedEntitySimpleInterface $entitity, TelaBotanicaUser $user): bool {
        return ( $user->getId() === $entitity->getUserId() );
    }

}
