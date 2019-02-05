<?php

namespace App\Elastica\Repository;

use App\Elastica\Query\Query;
use App\Elastica\Query\BaseQueryBuilder;
use App\Elastica\Query\PhotoQuery;
use App\Elastica\Query\PhotoQueryBuilder;


class PhotoRepository extends AbstractElasticRepository
{

    protected function requestToFindQuery($request): Query {
        return new PhotoQuery($request);
    }

    protected function getBuilder(): BaseQueryBuilder {
        return new PhotoQueryBuilder();
    }

}
