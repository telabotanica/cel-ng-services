<?php

namespace App\Service;

use App\Entity\Occurrence;
use App\Service\AbstractBulkService;

/**
 * Occurrence service for bulk operations (JSON-PATCH). Currently,  
 * 'remove', 'replace' and 'copy' (clone) atomic operations are 
 * allowed. 
 *
 * @package App\Service
 */
class OccurrenceBulkService extends AbstractBulkService {

    /**
     * @inheritdoc
     */
    protected function initForm($entity) {
        $this->form = $this->formFactory->create(Occurrence::class, $entity);
    }

    /**
     * @inheritdoc
     */
    protected function initRepo() {
        $this->repo = $this->doctrine->getRepository('App\Entity\Occurrence');
    }

}


