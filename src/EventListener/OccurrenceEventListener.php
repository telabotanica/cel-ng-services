<?php

// src/App/EventListener/OccurrenceEventListener.php
namespace App\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Occurrence;
use App\TelaBotanica\Eflore\Api\EfloreApiClient;

class OccurrenceEventListener
{
    public function prePersist(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();

        // only act on "Occurrence" entities
        if (!$entity instanceof Occurrence) {
            return;
        }

        $this->doCommon($entity);

        // If isPublic status has been set to true, set the occurrence 
        // datePublished to now:
        if ( $entity->getIsPublic() ) {
            $entity->setDatePublished((new \DateTime())->format('Y-m-d H:i:s'));
        }

        if ( null !== $entity->getTaxoRepo() ){
            $efloreClient = new EfloreApiClient();
            $familyName = $efloreClient->getFamilyName($entity->getUserSciNameId(), $entity->getTaxoRepo()->getName());
            if ( null !== $familyName ) {
                $entity->setFamily($familyName);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();

        // only act on "Occurrence" entities
        if (!$entity instanceof Occurrence) {
            return;
        }

        // If isPublic status has been changed to true, set the occurrence 
        // datePublished to now:
        if ($args->hasChangedField('isPublic') && $args->getNewValue('isPublic') == true) {
            $entity->setDatePublished((new \DateTime())->format('Y-m-d H:i:s'));
        }

        $this->doCommon($entity);


    }

    public function doCommon(Occurrence $occ)
    {

        // If the occurrence cannot be published:
        if ( ! ($occ->isPublishable()) ) {
            // Force it to be private:
            $occ->setIsPublic(false);
        }

        $occ->generateSignature();

    }

/*

    public function persistRelatedPhotos(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Occurrence) {
            return;
        }

        $entity->generateSignature();
    }
*/
}
