<?php

namespace App\Search;

use App\Search\PhotoSearchQueryBuilder;
use App\Search\OccurrenceSearch;

/**
 * Represents a search query for Photo resource type. 
 */
class PhotoSearch extends OccurrenceSearch
{
    private $dateShotDay;
    private $dateShotMonth;
    private $dateShotYear;
    

    public function __construct($request)
    {
        parent::__construct($request);
        $this->dateShotYear = $request->query->get('dateShotYear');
        $this->dateShotMonth = $request->query->get('dateShotMonth');
        $this->dateShotDay = $request->query->get('dateShotDay');
    }

	public function getDateShotYear(){
		return $this->dateShotYear;
	}

	public function setDateShotYear($dateShotYear){
		$this->dateShotYear = $dateShotYear;
	}

	public function getDateShotMonth(){
		return $this->dateShotMonth;
	}

	public function setDateShotMonth($dateShotMonth){
		$this->dateShotMonth = $dateShotMonth;
	}

	public function getDateShotDay(){
		return $this->dateShotDay;
	}

	public function setDateShotDay($dateShotDay){
		$this->dateShotDay = $dateShotDay;
	} 


}