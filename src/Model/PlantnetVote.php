<?php

namespace App\Model;

class PlantnetVote
{
    /**
     * Species name
     *
     * @var ?string
     */
    private $name;

    /**
     * Vote count for the species
     *
     * @var int
     */
    private $count;

    /**
     * @var PlantnetScore
     */
    private $score;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return PlantnetVote
     */
    public function setName(?string $name): PlantnetVote
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return PlantnetVote
     */
    public function setCount(int $count): PlantnetVote
    {
        $this->count = $count;
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
     * @return PlantnetVote
     */
    public function setScore(PlantnetScore $score): PlantnetVote
    {
        $this->score = $score;
        return $this;
    }
}
