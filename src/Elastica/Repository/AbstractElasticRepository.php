<?php

namespace App\Elastica\Repository;

use App\Elastica\Query\Query;
use App\Elastica\Query\BaseQueryBuilder;
use App\Entity\Photo;

use FOS\ElasticaBundle\Repository;

/**
 * Base abstract class for <code>ElasticRepository</code> interface.
 */
// @todo use generics...
abstract class AbstractElasticRepository extends Repository implements ElasticRepositoryInterface {
    
    /**
     * Returns a <code>FindQuery</code> built from the 
     *         provided HTTP request.
     *
     * @param the HTTP request containing the search/sort/pagination
     *        parameters.
     * @return a <code>FindQuery</code> built from the 
     *         provided HTTP request.
     */
    abstract protected function requestToFindQuery($request): Query;
    
    /**
     * Returns a <code>BaseQueryBuilder</code> for building (elastica)
     *         <code>Query</code>s.
     * 
     * @return a <code>BaseOccurrenceSearch</code>s built from the 
     *         provided HTTP request.
     */
    abstract protected function getBuilder(): BaseQueryBuilder;

    // @todo put default values in config
    public function findWithRequest($request, $user)  
    {
        $query = $this->requestToFindQuery($request);
        // build the query:
        $queryBuilder = $this->getBuilder();
        $esQuery = $queryBuilder->build($user, $query);

        // Let's paginate if asked for (we don't use fantapager here cos 
        // it's currentlybuggy in cunjunction with elastica):
        if ($query->isPaginated()) {
            $perPage = ( $query->getPerPage()>=1 ) ? $query->getPerPage() : 10;
            $esQuery->setFrom($query->getPerPage() * $query->getPage());
            $esQuery->setSize($perPage);
            return $this->find($esQuery, $perPage);
        }
        // No pagination, we want them all (or at least the 10000 first offered
        // by ES without using the scroll API)
        return $this->find($esQuery,10000);
    }


    /**
     * Returns 1... howmanyever the number of hits...
     */
    public function countWithRequest($request, $user)  
    {
        $query = $this->requestToFindQuery($request);
        $queryBuilder = $this->getBuilder();
        $esQuery = $queryBuilder->build($user, $query);
        $results = $this->findPaginated($esQuery);
        $results->setMaxPerPage(10);
        $results->setCurrentPage(1);
/*
        echo "NB OF DOCS FOR THIS PAGE=" . sizeof($results->getCurrentPageResults()) ."\n";
        echo "TOTAL NBR OF DOCS=" . $results->getNbResults()  ."\n";
*/
        return $results->getNbResults();
    }



}
