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

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Occurrence) {
            return;
        }

        $entity->generateSignature();

        $efloreClient = new EfloreApiClient();

        if ( null !== $entity->getTaxoRepo() ){
            $entity->setFamily($efloreClient->getFamilyName($entity->getUserSciNameId(), $entity->getTaxoRepo()->getName()));
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Occurrence) {
            return;
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
