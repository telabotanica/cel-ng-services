<?php

namespace App\Elastica\Query;

/**
 * Interface for queries on CEL resources. The resultset can be sorted and/or paginated.
 */
// @todo type this
interface QueryInterface {

    public function containsFilter();
    public function isPaginated();
    public function isSorted();
	public function getFreeTextQuery();
	public function setFreeTextQuery($freeTextQuery);
	public function getPage();
	public function setPage($page);
	public function getPerPage();
	public function setPerPage($perPage);
	public function getSortBy();
	public function setSortBy($sortBy);
	public function getSortDirection();
	public function setSortDirection($sortDirection);

}
