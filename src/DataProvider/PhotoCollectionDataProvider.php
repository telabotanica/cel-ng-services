<?php

namespace App\DataProvider;

use App\Entity\Photo;

/**
 * <code>BaseCollectionDataProvider</code> class for <code>Photo</code> 
 * entities.
 *
 * @package App\DataProvider
 */
final class PhotoCollectionDataProvider extends BaseCollectionDataProvider {

    /**
     * @inheritdoc
     */
    public function getResourceClass(): string {
        return Photo::class;
    }

}

