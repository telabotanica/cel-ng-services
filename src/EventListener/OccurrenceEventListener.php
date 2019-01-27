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

        $this->doCommon($occ);

        $efloreClient = new EfloreApiClient();

        if ( null !== $entity->getTaxoRepo() ){
            $familyName = $efloreClient->getFamilyName($entity->getUserSciNameId(), $entity->getTaxoRepo()->getName());
            if ( null !== $familyName ) {
                $entity->setFamily($familyName);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->doCommon($occ);


    }

    public function doCommon(Occurrence $occ)
    {
        $entity = $args->getEntity();

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Occurrence) {
            return;
        }
        // If the occurrence cannot be published:
        if ( ! ($entity->isPublishable()) ) {
            // Force it to be private:
            $entity->setIsPublic(false);
        }

        $entity->generateSignature();

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
