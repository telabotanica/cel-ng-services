<?php

namespace App\EventListener;

use App\Entity\TimestampedEntityInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;


class TimestampedEntityEventListener {

    public function prePersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        // only act on entities implementing "TimestampedEntityInterface" 
        if (!$entity instanceof TimestampedEntityInterface ) {
            return;
        }
        $entity->setDateCreated(new \DateTime("now"));
    }


    public function preUpdate(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        // only act on entities implementing "TimestampedEntityInterface" 
        if (!$entity instanceof TimestampedEntityInterface  ) {
            return;
        }
        $entity->setDateUpdated(new \DateTime("now"));  
    }

}
