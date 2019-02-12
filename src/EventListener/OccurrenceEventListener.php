<?php

namespace App\EventListener;

use App\Entity\Occurrence;
use App\TelaBotanica\Eflore\Api\EfloreApiClient;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Populates various properties of <code>Occurrence</code> instances 
 * based on CEL business rules before they are persisted/updated.
 * The properties can be "family", "datePublished" and "isPublic".
 *
 * @package App\EventListener
 */
class OccurrenceEventListener {

    /**
     * Populates various properties of <code>Occurrence</code> instances 
     * based on CEL business rules before they are persisted.
     *
     * @param LifecycleEventArgs $args The Lifecycle Event emitted.
     */
    public function prePersist(LifecycleEventArgs $args) { 
        $entity = $args->getEntity();

        // only act on "Occurrence" entities
        if (!$entity instanceof Occurrence) {
            return;
        }

        $this->doCommon($entity);

        // If isPublic status has been set to true, set the occurrence 
        // datePublished to now:
        if ( $entity->getIsPublic() ) {
            $entity->setDatePublished(new \DateTime());
        }

        if ( null !== $entity->getTaxoRepo() ){
            $efClient = new EfloreApiClient();
            $userSciNameId = $entity->getUserSciNameId();
            $taxoRepoName = $entity->getTaxoRepo()->getName();
            $familyName = $efClient->getFamilyName(
                $userSciNameId, $taxoRepoName);
            if ( null !== $familyName ) {
                $entity->setFamily($familyName);
            }
        }
    }

    /**
     * Populates various properties of <code>Occurrence</code> instances 
     * based on CEL business rules before they are updated.
     *
     * @param LifecycleEventArgs $args The Lifecycle Event emitted.
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        // only act on "Occurrence" entities
        if (!$entity instanceof Occurrence) {
            return;
        }

        // If isPublic status has been changed to true, set the occurrence 
        // datePublished to "now":
        if ( $args->hasChangedField('isPublic') && 
            $args->getNewValue('isPublic') == true) {

            $entity->setDatePublished(new \DateTime());
        }

        $this->doCommon($entity);


    }

    private function doCommon(Occurrence $occ) {
        // If the occurrence cannot be published:
        if ( ! ($occ->isPublishable()) ) {
            // Force it to be private:
            $occ->setIsPublic(false);
        }

        $occ->generateSignature();

    }

/*

    public function persistRelatedPhotos(LifecycleEventArgs $args) {
 
        $entity = $args->getEntity();

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Occurrence) {
            return;
        }

        $entity->generateSignature();
    }
*/
}
