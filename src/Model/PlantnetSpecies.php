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
	 * @var ?string
	 */
	private $powoId;
	
	/**
	 * @var ?string
	 */
	private $gbifId;

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
	
	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}
	
	/**
	 * @param string|null $author
	 */
	public function setAuthor(?string $author): void
	{
		$this->author = $author;
	}
	
	/**
	 * @param string $family
	 */
	public function setFamily(string $family): void
	{
		$this->family = $family;
	}
	
	/**
	 * @param string $genus
	 */
	public function setGenus(string $genus): void
	{
		$this->genus = $genus;
	}
	
	/**
	 * @return string|null
	 */
	public function getPowoId(): ?string
	{
		return $this->powoId;
	}
	
	/**
	 * @param string|null $powoId
	 */
	public function setPowoId(?string $powoId): void
	{
		$this->powoId = $powoId;
	}
	
	/**
	 * @return string|null
	 */
	public function getGbifId(): ?string
	{
		return $this->gbifId;
	}
	
	/**
	 * @param string|null $gbifId
	 */
	public function setGbifId(?string $gbifId): void
	{
		$this->gbifId = $gbifId;
	}
	
}
