<?php

namespace App\Vich;

use App\Entity\Photo;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer class. Called during the prePersist/preUpdate doctrine events.
 */
class TelaImageDirectoryNamer implements DirectoryNamerInterface {

    private $tmpFolder;
    private $baseTelaPhotoApiUrl;

    public function __construct(string $tmpFolder, string $baseTelaPhotoApiUrl)
    {
        $this->tmpFolder = $tmpFolder;
        $this->baseTelaPhotoApiUrl = $baseTelaPhotoApiUrl;
    }

    /**
     * @inheritdoc
     */
    public function directoryName($object, PropertyMapping $mapping): string {
        return ( null !== $object->getId() ) ? 
            $this->buildTelaPhotoApiFolderName($object) :
            $this->tmpFolder . '/';
    }

    /**
     * Returns the folder name associated with given entity.
     * 
     * @return string The folder name associated with given entity.
     */
    public function buildTelaPhotoApiFolderName(Photo $entity): string {
        $obsStrId = str_pad(strval($entity->getId()), 9, "0", STR_PAD_LEFT);
        return $this->baseTelaPhotoApiUrl . substr($obsStrId, 0, 3) . \DIRECTORY_SEPARATOR . substr($obsStrId, 3, 3) .  \DIRECTORY_SEPARATOR . 'O'  ;
    }

}

