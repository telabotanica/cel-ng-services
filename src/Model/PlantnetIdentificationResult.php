<?php

namespace App\Model;

class PlantnetIdentificationResult
{
    /**
     * @var ?string
     */
    private $species;

    /**
     * Between 0 and 1, 0 for unlikely, 1 for very likely
     * @var ?float
     */
    private $score;

    /**
     * @return string|null
     */
    public function getSpecies(): ?string
    {
        return $this->species;
    }

    /**
     * @param string|null $species
     * @return PlantnetIdentificationResult
     */
    public function setSpecies(?string $species): PlantnetIdentificationResult
    {
        $this->species = $species;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * @param float|null $score
     * @return PlantnetIdentificationResult
     */
    public function setScore(?float $score): PlantnetIdentificationResult
    {
        $this->score = $score;
        return $this;
    }
}
