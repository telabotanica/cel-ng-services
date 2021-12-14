<?php

namespace App\Model;

class PlantnetSpecies
{
    /**
     * Species name
     *
     * @var string
     */
    private $name;

    /**
     * @var ?string
     */
    private $author;

    /**
     * @var string
     */
    private $family;

    /**
     * @var string
     */
    private $genus;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getFamily(): string
    {
        return $this->family;
    }

    /**
     * @return string
     */
    public function getGenus(): string
    {
        return $this->genus;
    }
}
