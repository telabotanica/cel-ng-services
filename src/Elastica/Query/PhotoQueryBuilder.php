<?php

namespace App\Elastica\Query;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataoryManagerInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;

// @todo swich to instance not static 
class PhotoQueryBuilder extends BaseQueryBuilder
{

    /**
     */
    public function __construct()
    {
        parent::__construct( 
            array(
                'dateShotDay', 'dateShotMonth', 'dateShotYear', 
                'dateObservedDay', 'dateObservedMonth', 'dateObservedYear', 
                'family', 'isIdentiplanteValidated', 'identiplanteScore', 
                'userSciName', 'locality', 'country', 'county', 'isPublic', 
                'certainty', 'projectId', 'tags'), 
            array('family', 'station', 'annotation', 'userSciName', 'locality', 
                  'sublocality', 'environment', 'taxoRepo', 'certainty'), 
            array('id') 
        );
    }


}


