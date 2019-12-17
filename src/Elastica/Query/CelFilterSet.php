<?php

namespace App\Elastica\Query;

use Symfony\Component\HttpFoundation\Request;

/**
 * Base implementation of <code>CelFilterSetInterface</code>. Handles 
 * sort/pagination and free text queries.
 *
 * @package App\Elastica\Query
 */

// @todo make enum for sortDirection : DESC ASC
class CelFilterSet implements CelFilterSetInterface {
 
    const PERPAGE_PARAM_NAME        = 'perPage';   
    const PAGE_PARAM_NAME           = 'page';   
    const SORTBY_PARAM_NAME         = 'sortBy';   
    const SORTDIRECTION_PARAM_NAME  = 'sortDirection';
    const FREETEXTQUERY_PARAM_NAME  = 'freeTextQuery';

    private $freeTextQuery;
    // Pagination parameters:
    private $page;
    private $perPage;
    // Sort parameters:
    private $sortBy;
    private $sortDirection;

    public function __construct($request) {
        $this->fillWithParameters($request);
    }

    /**
     * Populates the <code>CelFilterSet</code> member variables with the
     * query parameter values from given HTTP <code>Request</code>.
     *
     * @param Request $request The HTTP <code>Request</code> containing the 
     *        query parameter.
     */
    protected function fillWithParameters(Request $request) {
        $this->freeTextQuery = $request->query->get(
            CelFilterSet::FREETEXTQUERY_PARAM_NAME);
        $this->page = $request->query->get(
            CelFilterSet::PAGE_PARAM_NAME);
        $this->perPage = $request->query->get(
            CelFilterSet::PERPAGE_PARAM_NAME);
        $this->sortBy = $request->query->get(
            CelFilterSet::SORTBY_PARAM_NAME);
        $this->sortDirection = $request->query->get(
            CelFilterSet::SORTDIRECTION_PARAM_NAME);
    }


    // @todo enable and tests
    public function containsFilter(): bool {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isPaginated(): bool {
		return (
            $this->page !== null && 
            $this->perPage !== null &&
            $this->page !== 'null' &&
            $this->perPage !== 'null'&&
            $this->page !== '' &&
            $this->perPage !== ''  );
	}

    /**
     * @inheritdoc
     */
    public function isSorted(): bool {

		return (
            $this->sortBy !== null && 
            $this->sortDirection !== null &&
            $this->sortBy !== 'null' &&
            $this->sortDirection !== 'null' &&
            $this->sortBy !== '' &&
            $this->sortDirection !== '' );
	}

	public function getFreeTextQuery(): ?string {
		return $this->freeTextQuery;
	}

	public function setFreeTextQuery(string $freeTextQuery) {
		$this->freeTextQuery = $freeTextQuery;
	}

	public function getPage(): int {
		return $this->page;
	}

	public function setPage(int $page) {
		$this->page = $page;
	}

	public function getPerPage(): int {
		return $this->perPage;
	}

	public function setPerPage(int $perPage) {
		$this->perPage = $perPage;
	}

	public function getSortBy(): string {
		return $this->sortBy;
	}

	public function setSortBy(string $sortBy) {
		$this->sortBy = $sortBy;
	}

	public function getSortDirection(): string {
		return $this->sortDirection;
	}

	public function setSortDirection(string $sortDirection) {
		$this->sortDirection = $sortDirection;
	}


}
