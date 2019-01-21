<?php

namespace App\Search;

// @todo make enum for sortDirection : DESC ASC
//@todo rename to BaseSearch, rename to FilterSet ?
class BaseOccurrenceSearch
{
    private $freeTextQuery;
    private $page;
    private $perPage;
    private $sortBy;
    private $sortDirection;

    public function __construct($request)
    {
        $this->freeTextQuery = $request->query->get('freeTextQuery');
        $this->page = $request->query->get('page');
        $this->perPage = $request->query->get('perPage');
        $this->sortBy = $request->query->get('sortBy');
        $this->sortDirection = $request->query->get('sortDirection');
    }

    // @todo enable and tests
    public function containsFilter()
    {
        return false;
    }

    public function isPaginated() {
		return ($this->page !== null && $this->perPage !== null );
	}

    public function isSorted(){
		return ($this->sortBy && $this->sortDirection);
	}

	public function getFreeTextQuery(){
		return $this->freeTextQuery;
	}

	public function setFreeTextQuery($freeTextQuery){
		$this->freeTextQuery = $freeTextQuery;
	}

	public function getPage(){
		return $this->page;
	}

	public function setPage($page){
		$this->page = $page;
	}

	public function getPerPage(){
		return $this->perPage;
	}

	public function setPerPage($perPage){
		$this->perPage = $perPage;
	}

	public function getSortBy(){
		return $this->sortBy;
	}

	public function setSortBy($sortBy){
		$this->sortBy = $sortBy;
	}

	public function getSortDirection(){
		return $this->sortDirection;
	}

	public function setSortDirection($sortDirection){
		$this->sortDirection = $sortDirection;
	}


}
