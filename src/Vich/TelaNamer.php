<?php

namespace App\Vich;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer class.
 */
class TelaNamer implements NamerInterface {

    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping): string {
        

        if ( null !== $object->getId() ) {
            return TelaNamer::buildFileName($entity);
        }
        else {
            return $mapping->getFile($object)->getClientOriginalName();
        }

    }


    public static function buildFileName($entity) {
        $obsStrId = str_pad(strval($entity->getId()), 9, "0", STR_PAD_LEFT);
        $ext  = substr(strrchr($entity->getOriginalName(),'.'),1);
        return substr($obsStrId, 0, 3) . '_' . substr($obsStrId, 3, 3) . '_' . substr($obsStrId, 6, 3) .  '_O.' . $ext;
    }

}

