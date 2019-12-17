<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Service\PhotoBulkService;

/**
 * Occurrence resource endpoint for bulk operations (JSON-PATCH). Currently,  
 * 'remove', 'replace' and 'copy' (clone) atomic operations are 
 * allowed. 
 *
 * @package App\Controller
 */
class PhotoBulkAction extends AbstractBulkAction {

    public function __construct(
        PhotoBulkService $abService) {

        $this->abService = $abService;
    }

}

