<?php

namespace App\DataProvider;

use App\Entity\Occurrence;

/**
 * <code>BaseCollectionDataProvider</code> class for <code>Occurrence</code> 
 * entities.
 */
final class OccurrenceCollectionDataProvider extends BaseCollectionDataProvider {

    /**
     * @inheritdoc
     */
    public function getResourceClass(): string {
        return Occurrence::class;
    }

}

