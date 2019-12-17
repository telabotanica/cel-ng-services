<?php

namespace App\Elastica\Repository;

use App\Elastica\Query\CelFilterSet;
use App\Elastica\Query\BaseQueryBuilder;
use App\Elastica\Query\OccurrenceFilterSet;
use App\Elastica\Query\OccurrenceQueryBuilder;

use Symfony\Component\HttpFoundation\Request;

/**
 * Implementation of <code>AbstractElasticRepository</code> dedicated to 
 * <code>Occurrence</code> entities/resources.
 *
 * @package App\Elastica\Repository
 */
class OccurrenceRepository extends AbstractElasticRepository {


    /**
     * @inheritdoc
     */
    protected function requestToFindQuery(Request $request): CelFilterSet {
        return new OccurrenceFilterSet($request);
    }

    /**
     * @inheritdoc
     */
    protected function getBuilder(): BaseQueryBuilder {
        return new OccurrenceQueryBuilder();
    }

    /**
     * @inheritdoc
     */
    protected function getEntityName(): string {
        return "occurrence";
    }


}
