<?php

namespace App\Elastica\Query;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataoryManagerInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Psr\Log\LoggerInterface;

// @todo swich to instance not static 
class OccurrenceQueryBuilder extends BaseQueryBuilder
{

    /**
     */
    public function __construct()
    {
        parent::__construct( 
            array(
                'dateObservedDay', 'dateObservedMonth', 'dateObservedYear', 
                'family', 'isIdentiplanteValidated', 'identiplanteScore', 
                'userSciName', 'locality', 'country', 'county', 'isPublic', 
                'certainty', 'projectId', 'signature', 'tags'), 
            array(
                'family', 'station', 'annotation', 'userSciName', 'locality', 
                'sublocality', 'environment', 'taxoRepo', 'certainty'), 
            array('id') );
    }


}



