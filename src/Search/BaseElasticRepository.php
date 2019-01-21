<?php

namespace App\Search;

use FOS\ElasticaBundle\Repository;
use App\Search\PhotoSearchQueryBuilder;
use App\Search\PhotoSearch;
use App\Entity\Photo;

abstract class BaseElasticRepository extends Repository
{


    abstract protected function requestToSearch($request);
    abstract protected function getBuilder();

    // @todo put default values in config
    public function findWithRequest($request, $user)  
    {
        $search = $this->requestToSearch($request);
        $queryBuilder = $this->getBuilder();
        $esQuery = $queryBuilder->build($user, $search);

        if ($search->isPaginated()) {
            $perPage = ( $search->getPerPage()>=1 ) ? $search->getPerPage() : 10;
            $esQuery->setFrom( $search->getPerPage() * $search->getPage() );
            $esQuery->setSize($perPage);
            return $this->find($esQuery, $perPage);
        }
        // no pagination, we want them all (or at least the 10000 first offered
        // by ES without using the scroll API
        return $this->find($esQuery,10000);
    }


}
