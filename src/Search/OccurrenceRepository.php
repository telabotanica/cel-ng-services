<?php

namespace App\Search;

use FOS\ElasticaBundle\Repository;
use App\Search\OccurrenceSearchQueryBuilder;

//@todo rename to OccElasticRepo
class OccurrenceRepository extends Repository
{

    // @todo put default values in config
    public function findWithRequest($request, $user)  
    {

        $search = new OccurrenceSearch($request);
        $queryBuilder = new OccurrenceSearchQueryBuilder();
        $esQuery = $queryBuilder->build($user, $search);

        if ($search->isPaginated()) {
            $perPage = ( $search->getPerPage()>=1 ) ? $search->getPerPage() : 10;
            $esQuery->setFrom( $search->getPerPage() * $search->getPage() );
            $esQuery->setSize($perPage);
            return $this->find($esQuery, $perPage);
        }

        return $this->find($esQuery,10000);
    }


    /**
     * Returns true if an Occurrence with same
     * locality/geometry/userId/observedDate/serSciName already exists.
     * Else returns false.
     *
     * @return bool Returns true if an Occurrence with the same
     * locality/geometry/userId/observedDate/userSciName already exists.
     */
    public function hasDuplicate($occ) : bool
    {
        if ($occ->getSignature() == null) {
            $occ->generateSignature();
        } 

        $result = $this->createQueryBuilder('p')
            ->andWhere('p.signature = :val')
            ->setParameter('val', $occ->getSignature())
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return ( sizeof($result) > 0 );
    }
}