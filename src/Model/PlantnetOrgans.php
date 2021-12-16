<?php

namespace App\Model;

// "organs_votes":{"leaf":{"plus":1,"score":{"pn":6.55,"total":6.55}}}
class PlantnetOrgans
{
    /**
     * @var ?PlantnetOrgansVotes
     */
    private $leaf;

    /**
     * @var ?PlantnetOrgansVotes
     */
    private $flower;

    /**
     * @var ?PlantnetOrgansVotes
     */
    private $fruit;

    /**
     * @var ?PlantnetOrgansVotes
     */
    private $bark;

    /**
     * @return PlantnetOrgansVotes|null
     */
    public function getLeaf(): ?PlantnetOrgansVotes
    {
        return $this->leaf;
    }

    /**
     * @param PlantnetOrgansVotes|null $leaf
     * @return PlantnetOrgans
     */
    public function setLeaf(?PlantnetOrgansVotes $leaf): PlantnetOrgans
    {
        $this->leaf = $leaf;
        return $this;
    }

    /**
     * @return PlantnetOrgansVotes|null
     */
    public function getFlower(): ?PlantnetOrgansVotes
    {
        return $this->flower;
    }

    /**
     * @param PlantnetOrgansVotes|null $flower
     * @return PlantnetOrgans
     */
    public function setFlower(?PlantnetOrgansVotes $flower): PlantnetOrgans
    {
        $this->flower = $flower;
        return $this;
    }

    /**
     * @return PlantnetOrgansVotes|null
     */
    public function getFruit(): ?PlantnetOrgansVotes
    {
        return $this->fruit;
    }

    /**
     * @param PlantnetOrgansVotes|null $fruit
     * @return PlantnetOrgans
     */
    public function setFruit(?PlantnetOrgansVotes $fruit): PlantnetOrgans
    {
        $this->fruit = $fruit;
        return $this;
    }

    /**
     * @return PlantnetOrgansVotes|null
     */
    public function getBark(): ?PlantnetOrgansVotes
    {
        return $this->bark;
    }

    /**
     * @param PlantnetOrgansVotes|null $bark
     * @return PlantnetOrgans
     */
    public function setBark(?PlantnetOrgansVotes $bark): PlantnetOrgans
    {
        $this->bark = $bark;
        return $this;
    }
}
