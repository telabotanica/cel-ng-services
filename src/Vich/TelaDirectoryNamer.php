<?php

namespace App\Vich;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer class.
 */
class TelaDirectoryNamer implements DirectoryNamerInterface {

    /**
     * {@inheritdoc}
     */
    public function directoryName($obj, PropertyMapping $mapping): string {

        if ( null !== $obj->getId() ) {
            return TelaDirectoryNamer::buildTelaPhotoApiFolder($obj);
        }
        else {
            return getEnv("TMP_FOLDER");
        }
    }

    public static function buildTelaPhotoApiFolder($entity) {
        $obsStrId = str_pad(strval($entity->getId()), 9, "0", STR_PAD_LEFT);
        return getEnv('BASE_TELA_PHOTO_API_DIR') . \DIRECTORY_SEPARATOR . substr($obsStrId, 0, 3) . \DIRECTORY_SEPARATOR . substr($obsStrId, 3, 3) .  \DIRECTORY_SEPARATOR . 'O' . \DIRECTORY_SEPARATOR ;
    }


}

