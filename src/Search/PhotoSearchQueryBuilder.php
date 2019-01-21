<?php

namespace App\Search;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataoryManagerInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;

use App\Entity\Occurrence;
use App\Search\PhotoSearchQueryBuilder;
use App\Search\BaseSearchQueryBuilder;

// @todo swich to instance not static 
class PhotoSearchQueryBuilder extends BaseSearchQueryBuilder
{

    /**
     */
    public function __construct()
    {
        parent::__construct( array('dateShotDay', 'dateShotMonth', 'dateShotYear', 'dateObservedDay', 'dateObservedMonth', 'dateObservedYear', 'family', 'isIdentiplanteValidated', 'identiplanteScore', 'userSciName', 'locality', 'country', 'county', 'isPublic', 'certainty', 'projectId', 'tags'), array('family', 'station', 'annotation', 'userSciName', 'locality', 'sublocality', 'environment', 'taxoRepo', 'certainty'), array('id') );
    }


}


