<?php

namespace App\Controller;

use App\Entity\Occurrence;
use App\Service\OccurrenceBulkService;

/**
 * Occurrence resource endpoint for bulk operations (JSON-PATCH). Currently,  
 * 'remove', 'replace' and 'copy' (clone) atomic operations are 
 * allowed. 
 *
 * @package App\Controller
 */
class OccurrenceBulkAction extends AbstractBulkAction {

    public function __construct(
        OccurrenceBulkService $abService) {

        $this->abService = $abService;
    }

}


