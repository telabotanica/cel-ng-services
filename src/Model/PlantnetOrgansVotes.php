<?php

namespace App\Model;

class PlantnetOrgansVotes
{
    /**
     * Count of upvote (cannot be downvoted)
     * @var int
     */
    private $plus;

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
     * @return PlantnetOrgansVotes
     */
    public function setPlus(int $plus): PlantnetOrgansVotes
    {
        $this->plus = $plus;
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
     * @return PlantnetOrgansVotes
     */
    public function setScore(PlantnetScore $score): PlantnetOrgansVotes
    {
        $this->score = $score;
        return $this;
    }
}
