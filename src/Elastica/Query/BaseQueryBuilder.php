<?php

namespace App\Elastica\Query;

use App\Security\User\TelaBotanicaUser;
use App\Security\User\UnloggedAccessException;
use App\Security\Elastica\AccessControlQueryBuilder;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchPhrase;
use Elastica\Query\Match;

/*
 * Builds elastica <code>Query</code>s from provided 
 * CEL <code>Query</code> and enhanced with the access control
 * filters for current <code>TelaBotanicaUser</code>.
 *
 * @package App\Elastica\Query
 */
class BaseQueryBuilder implements QueryBuilderInteface {

    // the default sort direction:
    const DEFAULT_SORT_DIRECTION = 'DESC';
    // the default sort property:
    const DEFAULT_SORT_BY        = 'dateCreated';   

    // The names of the filterable fields (atomic value):
    protected $allowedFilterFields = array();
    // The names of of the filterable fields (arrays):
    protected $allowedFilterArrayFields = array();
    // The names of the fields the free text search concerns:
    protected $freeTextSearchFields = array();

    /**
     * Returns a new <code>BaseQueryBuilder</code> instance.
     */
    public function __construct(array $allowedFilterFields, array $freeTextSearchFields, array $allowedFilterArrayFields) {
        $this->allowedFilterFields = $allowedFilterFields;
        $this->freeTextSearchFields = $freeTextSearchFields;
        $this->allowedFilterArrayFields = $allowedFilterArrayFields;
    }

    protected function addMustQueryIfNeeded($fFilter, $occSearch, $fieldName) {
        $query = null;
        $getttterName = 'get' . ucfirst($fieldName);

        if ( (null !== $occSearch->$getttterName()) && ('' !== $occSearch->$getttterName()) ) {
            $query = new MatchPhrase();
            $query->setField($fieldName, $occSearch->$getttterName());
            $fFilter->addMust($query);
        }

        return $fFilter;
    }

    protected function addMustArrayQueryIfNeeded($fFilter, $occSearch, $fieldName) {
        $getttterName = 'get' . ucfirst($fieldName);

        if (null !== $occSearch->$getttterName()) {

            $valueArray = $occSearch->$getttterName();
            if (sizeof($valueArray)>0) {
                $orBoolQuery = new BoolQuery();
                foreach($valueArray as $value) {
                    $orBoolQuery = $this->addShouldMatchPhraseQuery($orBoolQuery, $value, $fieldName);
                }           
                $fFilter = $fFilter->addMust($orBoolQuery);
            }
        }

        return $fFilter;
    }

    protected function addShouldMatchPhraseQuery($fFilter, $strQuery, $fieldName) {
        $query = new MatchPhrase();
        $query->setField($fieldName, $strQuery);
        $fFilter->addShould($query);

        return $fFilter;
    }

    protected function addShouldMatchQuery($fFilter, $strQuery, $fieldName) {
        $query = new Match();
        $query->setField($fieldName, $strQuery);
        $fFilter->addShould($query);

        return $fFilter;
    }

    /**
     * @inheritdoc
     */
    public function build(?TelaBotanicaUser $user, CelFilterSetInterface $filterSet) : Query {
        $esQuery = new Query();
        $globalQuery = new BoolQuery();
        $acQuery = $this->buildAccessControlQuery($user);


        if ( null !== $acQuery) {   
            $globalQuery->addMust($acQuery);
        }

        if ($filterSet->containsFilter()) {
            $filterQuery = $this->buildFilterQuery($filterSet);
            if ( null !== $filterQuery) {   
                $globalQuery->addMust($filterQuery);
            }
        }

        // handle the free text query : addShould filters  
        $freeTextStrQuery = $filterSet->getFreeTextQuery();
        if ( (null !== $freeTextStrQuery) && ('' !== $freeTextStrQuery) ) {
            $ftQuery = $this->buildFreeTextQuery($filterSet, $freeTextStrQuery);
            if ( null !== $ftQuery) {   
                $globalQuery->addMust($ftQuery);
            }
        }

        $esQuery->setQuery($globalQuery);

        // @refactor: put these in conf
        // No sort parameters provided, add default ones:
        if ( ! $filterSet->isSorted() ) {
            $filterSet->setSortDirection(BaseQueryBuilder::DEFAULT_SORT_DIRECTION);
            $filterSet->setSortBy(BaseQueryBuilder::DEFAULT_SORT_BY);
        }

        $esQuery = $this->customizeWithSortParameters($esQuery, $filterSet);
        // Pretty handy to debug:
        //die(json_encode(["query" =>$esQuery->getQuery()->toArray()]));

        return $esQuery;
    }



    protected function buildFreeTextQuery($occSearch, $freeTextQuery)
    {
        $ftFilter = new BoolQuery();

        foreach ($this->freeTextSearchFields as $fieldName){
            $ftFilter =  $this->addShouldMatchPhraseQuery($ftFilter, $freeTextQuery, $fieldName);
        }

        return $ftFilter;
    }

    /**
     *  
     */
    protected function buildFilterQuery($occSearch) {

        $fFilter = new BoolQuery();
        // @todo put this in conf        

        foreach ($this->allowedFilterFields as $fieldName){
            $fFilter = $this->addMustQueryIfNeeded($fFilter, $occSearch, $fieldName);
        }

        foreach ($this->allowedFilterArrayFields as $fieldName){
            $fFilter = $this->addMustArrayQueryIfNeeded($fFilter, $occSearch, $fieldName);
        }

        return $fFilter;
    }


    /**
     * Returns the elastica access control <code>Match</code> query for a given user
     * based on her/his access level.
     *
     * @internal delegates to App\Security\Elastica\AccessControlQueryBuilder
     * @param TelaBotanicaUser $user The user to generate the access controle
     *        query for.
     * @return the elastica access control <code>Match</code> query for
     *          given <code>TelaBotanicaUser</code> based on her/his access level.
     */ 
    protected function buildAccessControlQuery(TelaBotanicaUser $user): Match {

        if ( $user === null ) {
            throw new UnloggedAccessException('You must be logged into tela-botanica SSO system to access this part of the app.');
        } 

        else {
            $acQueryBuilder = new AccessControlQueryBuilder();
            return $acQueryBuilder->build($user);
        }

    }

    protected function customizeWithSortParameters($esQuery, $occSearch) {
        // We use the keyword typed version of the property for sorting:
        $esQuery->addSort(
            [ $occSearch->getSortBy() . '_keyword' => 
                [
                    'order' => $occSearch->getSortDirection() 
                ]
            ]
        );

        return $esQuery;
    }

}



