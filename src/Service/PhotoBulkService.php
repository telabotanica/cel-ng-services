<?php

namespace App\Service;

use App\Entity\Photo;
use App\Service\AbstractBulkService;

/**
 * Photo service for bulk operations (JSON-PATCH). Currently,  
 * 'remove', 'replace' and 'copy' (clone) atomic operations are 
 * allowed. 
 *
 * @package App\Service
 */
class PhotoBulkService extends AbstractBulkService {

    /**
     * @inheritdoc
     */
    protected function initForm($entity) {
        $this->form = $this->formFactory->create(Photo::class, $entity);
    }

    /**
     * @inheritdoc
     */
    protected function initRepo() {
        $this->repo = $this->doctrine->getRepository('App\Entity\Photo');
    }

}


