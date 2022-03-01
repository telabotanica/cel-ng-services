<?php

namespace App\Vich;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer class.  * Namer class. Seems to be called during the preUpdate doctrine events.
 */
class TelaNamer implements NamerInterface
{
    public const ALLOWED_EXTENSION = [
        'jpeg',
        'jpg',
        'png'
    ];

    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping): string {
        return ( null !== $object->getId() ) ? 
            TelaNamer::buildTelaPhotoApiFileName($entity) : 
            $mapping->getFile($object)->getClientOriginalName();

 /*
        if ( null !== $object->getId() ) {
            return TelaNamer::buildFileName($entity);
        }
        else {
            return $mapping->getFile($object)->getClientOriginalName();
        }
*/
    }

    /**
     * Returns the file name for the photo in tela's photo API, id-based format.
     *         e.g. "000_000_252_O.png"
     * @return the file name for the photo in tela's photo API, id-based format.
     */
    public static function buildTelaPhotoApiFileName($entity): string {
        // stretch the id to a 9 digits string:
        $obsStrId = str_pad(strval($entity->getId()), 9, "0", STR_PAD_LEFT);
        // retrieve the file extension based on its original name:
        // @refactor use getMimeType() instead...
        $ext  = substr(strrchr($entity->getOriginalName(),'.'),1);
        if (!$ext) {
            $ext = 'jpg';
        }

        return substr($obsStrId, 0, 3) . '_' . substr($obsStrId, 3, 3) . '_' . substr($obsStrId, 6, 3) .  '_O.' . $ext;
    }


    /**
     * Returns the file name for the photo URL in tela's photo API, id-based
     * format e.g. "000000252O.png"
     *
     * @return string photo filename
     */
    public static function buildTelaPhotoApiUrlFileName($entity): string {
        // stretch the id to a 9 digits string:
        $obsStrId = str_pad((string) $entity->getId(), 9, '0', STR_PAD_LEFT);
        // retrieve the file extension based on its original name:
        $ext = pathinfo($entity->getOriginalName(), PATHINFO_EXTENSION);
        if (!in_array($ext, self::ALLOWED_EXTENSION, true)) {
            $ext = 'jpg';
        }

        return $obsStrId .  'O.' . $ext;
    }

}

