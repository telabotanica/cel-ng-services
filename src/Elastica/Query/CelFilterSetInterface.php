<?php

namespace App\Elastica\Query;

/**
 * Interface for sets of filtering parameters (queries) on CEL 
 * resources/entities. The resultset can be sorted and/or paginated.
 * 
 * A <code>CelFilterSetInterface</code> is a set of predifined filters on a 
 * given CEL resource properties AND a set of pagination/sort parameters.
 *
 * @package App\Elastica\Query
 */
interface CelFilterSetInterface {

    public function containsFilter(): bool;
    public function isPaginated(): bool;
    public function isSorted(): bool;
	public function getFreeTextQuery(): ?string;
	public function setFreeTextQuery(string $freeTextQuery);
	public function getPage(): int;
	public function setPage(int $page);
	public function getPerPage(): int;
	public function setPerPage(int $perPage);
	public function getSortBy(): string ;
	public function setSortBy(string $sortBy);
	public function getSortDirection(): string ;
	public function setSortDirection(string $sortDirection);

}
