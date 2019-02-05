<?php

namespace App\Elastica\Repository;

use FOS\ElasticaBundle\Repository;
use App\Entity\Photo;

/**
 * Base abstract class for <code>ElasticRepository</code> interface.
 */
interface ElasticRepositoryInterface {

    public function findWithRequest($request, $user);
    public function countWithRequest($request, $user);  

}
