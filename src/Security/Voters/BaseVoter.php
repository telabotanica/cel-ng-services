<?php

namespace App\Security\Voters;

use App\Entity\PhotoTag;
use App\Entity\UserOccurrenceTag;
use App\Entity\UserProfileCel;
use App\Security\User\TelaBotanicaUser;
use App\Security\Voters\AbstractVoter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security <code>AbstractVoter</code> for:
 *
 * <ul>
 *   <li><code>UserProfileCel</code></li> 
 *   <li><code>PhotoTag</code></li>
 *   <li><code>UserOccurrenceTag</code></li>
 * </ul>
 *
 * resources/entities. 
 *
 * Only the owner or a TelaBotanica admin can create/read/update/delete 
 * instances.
 *
 * @package App\Security\Voters
 */
class BaseVoter extends AbstractVoter {

    /**
     * @inheritdoc
     */
    protected function supportsEntity($subject): bool {

        if ( !( $subject instanceof UserProfileCel ) ||   
            !( $subject instanceof PhotoTag ) ||
            !( $subject instanceof UserOccurrenceTag ) ) {

            return false;
        }

        return true;
    }

}
