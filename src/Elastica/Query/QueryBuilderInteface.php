<?php

namespace App\Elastica\Query;

use App\Entity\Occurrence;
use App\Security\User\TelaBotanicaUser;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataoryManagerInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;

/**
 * Rename to ElasticaQueryBuilder
 */

interface QueryBuilderInteface {

    public function build(TelaBotanicaUser $user, QueryInterface $occSearch): Query;

}



