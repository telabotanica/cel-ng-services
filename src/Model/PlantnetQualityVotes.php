<?php

namespace App\Model;

class PlantnetQualityVotes
{
    //{"quality_votes":{"plus":1,"minus":0,"score":{"pn":6.55,"total":6.55}}}
    /**
     * @var int
     */
    private $plus;

    /**
     * @var int
     */
    private $minus;

    /**
     * @var PlantnetScore
     */
    private $score;

    /**
     * @return int
     */
    public function getPlus(): int
    {
        return $this->plus;
    }

    /**
     * @param int $plus
     * @return PlantnetQualityVotes
     */
    public function setPlus(int $plus): PlantnetQualityVotes
    {
        $this->plus = $plus;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinus(): int
    {
        return $this->minus;
    }

    /**
     * @param int $minus
     * @return PlantnetQualityVotes
     */
    public function setMinus(int $minus): PlantnetQualityVotes
    {
        $this->minus = $minus;
        return $this;
    }

    /**
     * @return PlantnetScore
     */
    public function getScore(): PlantnetScore
    {
        return $this->score;
    }

    /**
     * @param PlantnetScore $score
     * @return PlantnetQualityVotes
     */
    public function setScore(PlantnetScore $score): PlantnetQualityVotes
    {
        $this->score = $score;
        return $this;
    }
}
