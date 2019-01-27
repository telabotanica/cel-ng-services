<?php

namespace App\Search;

use App\Entity\Occurrence;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataoryManagerInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;

// @todo swich to instance not static 
abstract class BaseSearchQueryBuilder
{

    // @todo put these in conf        
    protected $allowedFilterFields = array();
    protected $allowedFilterArrayFields = array();
    protected $freeTextSearchFields = array();

    /**
     */
    public function __construct(array $allowedFilterFields, array $freeTextSearchFields, array $allowedFilterArrayFields)
    {
        $this->allowedFilterFields = $allowedFilterFields;
        $this->freeTextSearchFields = $freeTextSearchFields;
        $this->allowedFilterArrayFields = $allowedFilterArrayFields;
    }

    protected function addMustQueryIfNeeded($fFilter, $occSearch, $fieldName)
    {
        $query = null;
        $getttterName = 'get' . ucfirst($fieldName);

        if ( (null !== $occSearch->$getttterName()) && ('' !== $occSearch->$getttterName()) ) {
            $query = new Match();
            $query->setField($fieldName, $occSearch->$getttterName());
            $fFilter->addMust($query);
        }

        return $fFilter;
    }


    protected function addMustArrayQueryIfNeeded($fFilter, $occSearch, $fieldName)
    {

        $getttterName = 'get' . ucfirst($fieldName);

        if (null !== $occSearch->$getttterName()) {
            $valueArray = $occSearch->$getttterName();
            if (sizeof($valueArray)>0) {
                $orBoolQuery = new BoolQuery();
                foreach($valueArray as $value) {

                    $orBoolQuery = $this->addShouldQuery($orBoolQuery, intval($value), $fieldName);
                }           
                $fFilter = $fFilter->addMust($orBoolQuery);

            }
        }

        return $fFilter;
    }

    protected function addShouldQuery($fFilter, $strQuery, $fieldName)
    {
        $query = new Match();
        $query->setField($fieldName, $strQuery);
        $fFilter->addShould($query);

        return $fFilter;
    }

    public function build($user, $occSearch)
    {
        $esQuery = new Query();
        $globalQuery = new BoolQuery();
        $acQuery = $this->buildAccessControlQuery($user);

        if ( null !== $acQuery) {   
            $globalQuery->addMust($acQuery);
        }

        if ($occSearch->containsFilter()) {
            $filterQuery = $this->buildFilterQuery($occSearch);
            if ( null !== $filterQuery) {   
                $globalQuery->addMust($filterQuery);
            }
        }

        // handle the free text query : addShould filters  
        $freeTextStrQuery = $occSearch->getFreeTextQuery();
        if ( (null !== $freeTextStrQuery) && ('' !== $freeTextStrQuery) ) {
            $ftQuery = $this->buildFreeTextQuery($occSearch, $freeTextStrQuery);
            if ( null !== $ftQuery) {   
                $globalQuery->addMust($ftQuery);
            }
        }
        // @refactor: put these in conf
        // No sort parameters provided, add default ones:
        if ( ! $occSearch->isSorted() ) {
            $occSearch->setSortDirection('DESC');
            $occSearch->setSortBy('dateCreated');
        }

        $esQuery = $this->customizeWithSortParameters($esQuery, $occSearch);

        return $esQuery;
    }



    protected function buildFreeTextQuery($occSearch, $freeTextQuery)
    {
        $ftFilter = new BoolQuery();

        foreach ($this->freeTextSearchFields as $fieldName){
            $ftFilter =  $this->addShouldQuery($ftFilter, $freeTextQuery, $fieldName);
        }

        return $ftFilter;
    }

    /**
     *  
     */
    protected function buildFilterQuery($occSearch)
    {

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
     */ 
    protected function buildAccessControlQuery($user)
    {

        $acQuery = null;

        if (!$user->isTelaBotanicaAdmin()) {
            // Project admins: limit to occurrence belonging to the project
            if ($user->isProjectAdmin()) {
                $acQuery = new Match();
                $acQuery->setField("projectId", $user->getAdministeredProjectId());
            }
            // Simple users: limit to her/his occurrences
            else if (!is_null($user)){
                $acQuery = new Match();
                $acQuery->setField("userId", $user->getId());
            }
            // Not even logged in user: limit to only public occurrences
            else {
                $acQuery = new Match();
                $acQuery->setField("isPublic", true);
            }
        }
        // Tela-botanica admin: no restrictions!

        return $acQuery;
    }

    /**
     */
    protected function customizeWithSortParameters($esQuery, $occSearch)
    {
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



