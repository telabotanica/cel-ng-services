<?php

namespace App\Model;

class PlantnetImage
{
    /**
     * @var string
     */
    private $id;

    /**
     * Thumbnail url "original" size (lot of 675px x 900px, some are bigger, maybe 1.200px max)
     * @var string
     */
    private $o;

    /**
     * Thumbnail url "medium" size (600px squared)
     * @var string
     */
    private $m;

    /**
     * Thumbnail url "small" size (150px squared)
     * @var string
     */
    private $s;

    /**
     * Something in "leaf", "flower", "fruit", "bark", "habit", "other"
     * @var ?string
     */
    private $organ;

    /**
     * @var bool
     */
    private $deleted;

    /**
     * If true, image doesn't show a plant
     * @var bool
     */
    private $noplant;

    /**
     * @var PlantnetQualityVotes
     */
    private $qualityVotes;

    /**
     * @var PlantnetOrgans
     */
    private $organsVotes;

    /**
     * @var ?int
     */
    private $partnerId;

    /**
     * @var ?string
     */
    private $partnerUrl;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PlantnetImage
     */
    public function setId(string $id): PlantnetImage
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getO(): string
    {
        return $this->o;
    }

    /**
     * @param string $o
     * @return PlantnetImage
     */
    public function setO(string $o): PlantnetImage
    {
        $this->o = $o;
        return $this;
    }

    /**
     * @return string
     */
    public function getM(): string
    {
        return $this->m;
    }

    /**
     * @param string $m
     * @return PlantnetImage
     */
    public function setM(string $m): PlantnetImage
    {
        $this->m = $m;
        return $this;
    }

    /**
     * @return string
     */
    public function getS(): string
    {
        return $this->s;
    }

    /**
     * @param string $s
     * @return PlantnetImage
     */
    public function setS(string $s): PlantnetImage
    {
        $this->s = $s;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrgan(): ?string
    {
        return $this->organ;
    }

    /**
     * @param string|null $organ
     * @return PlantnetImage
     */
    public function setOrgan(?string $organ): PlantnetImage
    {
        $this->organ = $organ;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return PlantnetImage
     */
    public function setDeleted(bool $deleted): PlantnetImage
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNoplant(): bool
    {
        return $this->noplant;
    }

    /**
     * @param bool $noplant
     * @return PlantnetImage
     */
    public function setNoplant(bool $noplant): PlantnetImage
    {
        $this->noplant = $noplant;
        return $this;
    }

    /**
     * @return PlantnetQualityVotes
     */
    public function getQualityVotes(): PlantnetQualityVotes
    {
        return $this->qualityVotes;
    }

    /**
     * @param PlantnetQualityVotes $qualityVotes
     * @return PlantnetImage
     */
    public function setQualityVotes(PlantnetQualityVotes $qualityVotes): PlantnetImage
    {
        $this->qualityVotes = $qualityVotes;
        return $this;
    }

    /**
     * @return PlantnetOrgans
     */
    public function getOrgansVotes(): PlantnetOrgans
    {
        return $this->organsVotes;
    }

    /**
     * @param PlantnetOrgans $organsVotes
     * @return PlantnetImage
     */
    public function setOrgansVotes(PlantnetOrgans $organsVotes): PlantnetImage
    {
        $this->organsVotes = $organsVotes;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPartnerId(): ?int
    {
        return $this->partnerId;
    }

    /**
     * @param int|null $partnerId
     * @return PlantnetImage
     */
    public function setPartnerId(?int $partnerId): PlantnetImage
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerUrl(): ?string
    {
        return $this->partnerUrl;
    }

    /**
     * @param string|null $partnerUrl
     * @return PlantnetImage
     */
    public function setPartnerUrl(?string $partnerUrl): PlantnetImage
    {
        $this->partnerUrl = $partnerUrl;
        return $this;
    }

    public function getOriginalImageUrl(): string
    {
        return $this->getO();
    }
}
