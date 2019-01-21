<?php

namespace App\Search;

use FOS\ElasticaBundle\Repository;
use App\Search\PhotoSearchQueryBuilder;
use App\Search\PhotoSearch;
use App\Entity\Photo;


class PhotoRepository extends BaseElasticRepository
{

    protected function requestToSearch($request) {
        return new PhotoSearch($request);
    }

    protected function getBuilder() {
        return new PhotoSearchQueryBuilder();
    }

}
