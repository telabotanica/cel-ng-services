<?php

namespace App\Elastica\Repository;

use App\Elastica\Query\CelFilterSet;
use App\Elastica\Query\BaseQueryBuilder;
use App\Elastica\Query\PhotoQueryBuilder;

use Symfony\Component\HttpFoundation\Request;

/**
 * Implementation of <code>AbstractElasticRepository</code> dedicated to 
 * <code>Photo</code> entities/resources.
 *
 * @package App\Elastica\Repository
 */
class PhotoRepository extends AbstractElasticRepository {

    /**
     * @inheritdoc
     */
    protected function requestToFindQuery(Request $request): CelFilterSet {
        return new PhotoFilterSet($request);
    }

    /**
     * @inheritdoc
     */
    protected function getBuilder(): BaseQueryBuilder {
        return new PhotoQueryBuilder();
    }

    /**
     * @inheritdoc
     */
    protected function getEntityName(): string {
        return "photo";
    }

}
