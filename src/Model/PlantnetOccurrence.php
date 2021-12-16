<?php

namespace App\Model;

class PlantnetOccurrence
{

    /**
     * @var int|string
     */
    private $id;

    /**
     * @var PlantnetAuthor
     */
    private $author;

    /**
     * @var PlantnetPartner|null
     */
    private $partner;

    /**
     * If dateObs is 0 then date is unknown and not Epoch (null date is not supported by plantnet code yet)
     * @var ?\DateTime|null
     */
    private $dateObs;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $dateUpdated;

    /**
     * Could be "©" for private or else any other public licence
     * Something in "©", "cc-by-sa", "cc-by-nc", "cc-by-nc-sa", "cc-by", "public", "gpl"
     * But others can be added later
     *
     * @var string
     */
    private $license;

    /**
     * Whether the occurrence is good quality or not
     * A valid occurrence is not deleted, not censured, has at least one image, has a "good" score, etc
     * It's sum or arbitrary criteria
     * Could lead to a public usage (taxa picture, etc)
     *
     * @var bool
     */
    private $isValid;

    /**
     * Deleted, please delete
     * @var bool
     */
    private $deleted;

    /**
     * Censored, please delete
     * @var bool
     */
    private $censored;

    /**
     * Full projects list: https://my-api.plantnet.org/v2/projects?lang=fr
     * @var string
     */
    private $project;

    /**
     * @var ?PlantnetSpecies
     */
    private $species;

    /**
     * Present accepted name, according to votes
     * @var ?string
     */
    private $currentName;

    /**
     * Initial name given by author at occurrence creation
     * @var ?string
     */
    private $submittedName;

    /**
     * @var PlantnetImage[]
     */
    private $images;

    /**
     * @var PlantnetIdentificationResult[]
     */
    private $identificationResults;

    /**
     * @var PlantnetGeo
     */
    private $geo;

    /**
     * @var PlantnetVote[]
     */
    private $votes;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|int $id
     * @return PlantnetOccurrence
     */
    public function setId($id): PlantnetOccurrence
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * @return PlantnetAuthor
     */
    public function getAuthor(): PlantnetAuthor
    {
        return $this->author;
    }

    /**
     * @param PlantnetAuthor $author
     * @return PlantnetOccurrence
     */
    public function setAuthor(PlantnetAuthor $author): PlantnetOccurrence
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return PlantnetPartner|null
     */
    public function getPartner(): ?PlantnetPartner
    {
        return $this->partner;
    }

    /**
     * @param PlantnetPartner|null $partner
     * @return PlantnetOccurrence
     */
    public function setPartner(?PlantnetPartner $partner): PlantnetOccurrence
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return ?\DateTime|null
     */
    public function getDateObs()
    {
        return $this->dateObs;
    }

    /**
     * @param ?\DateTime|null $dateObs
     * @return PlantnetOccurrence
     */
    public function setDateObs($dateObs): PlantnetOccurrence
    {
        $this->dateObs = $dateObs;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     * @return PlantnetOccurrence
     */
    public function setDateCreated(\DateTime $dateCreated): PlantnetOccurrence
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated(): \DateTime
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     * @return PlantnetOccurrence
     */
    public function setDateUpdated(\DateTime $dateUpdated): PlantnetOccurrence
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     * @return PlantnetOccurrence
     */
    public function setLicense(string $license): PlantnetOccurrence
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     * @return PlantnetOccurrence
     */
    public function setIsValid(bool $isValid): PlantnetOccurrence
    {
        $this->isValid = $isValid;
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
     * @return PlantnetOccurrence
     */
    public function setDeleted(bool $deleted): PlantnetOccurrence
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCensored(): bool
    {
        return $this->censored;
    }

    /**
     * @param bool $censored
     * @return PlantnetOccurrence
     */
    public function setCensored(bool $censored): PlantnetOccurrence
    {
        $this->censored = $censored;
        return $this;
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @param string $project
     * @return PlantnetOccurrence
     */
    public function setProject(string $project): PlantnetOccurrence
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return ?PlantnetSpecies
     */
    public function getSpecies(): ?PlantnetSpecies
    {
        return $this->species;
    }

    /**
     * @param ?PlantnetSpecies $species
     * @return PlantnetOccurrence
     */
    public function setSpecies(?PlantnetSpecies $species): PlantnetOccurrence
    {
        $this->species = $species;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getCurrentName(): ?string
    {
        return $this->currentName;
    }

    /**
     * @param ?string $currentName
     * @return PlantnetOccurrence
     */
    public function setCurrentName(?string $currentName): PlantnetOccurrence
    {
        $this->currentName = $currentName;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getSubmittedName(): ?string
    {
        return $this->submittedName;
    }

    /**
     * @param ?string $submittedName
     * @return PlantnetOccurrence
     */
    public function setSubmittedName(?string $submittedName): PlantnetOccurrence
    {
        $this->submittedName = $submittedName;
        return $this;
    }

    /**
     * @return PlantnetImage[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param PlantnetImage[] $images
     * @return PlantnetOccurrence
     */
    public function setImages(array $images): PlantnetOccurrence
    {
        $this->images = $images;
        return $this;
    }

    /**
     * @return PlantnetIdentificationResult[]
     */
    public function getIdentificationResults(): array
    {
        return $this->identificationResults;
    }

    /**
     * @param PlantnetIdentificationResult[] $identificationResults
     * @return PlantnetOccurrence
     */
    public function setIdentificationResults(array $identificationResults): PlantnetOccurrence
    {
        $this->identificationResults = $identificationResults;
        return $this;
    }

    /**
     * @return PlantnetGeo
     */
    public function getGeo(): PlantnetGeo
    {
        return $this->geo;
    }

    /**
     * @param PlantnetGeo $geo
     * @return PlantnetOccurrence
     */
    public function setGeo(PlantnetGeo $geo): PlantnetOccurrence
    {
        $this->geo = $geo;
        return $this;
    }

    /**
     * @return PlantnetVote[]
     */
    public function getVotes(): array
    {
        return $this->votes;
    }

    /**
     * @param PlantnetVote[] $votes
     * @return PlantnetOccurrence
     */
    public function setVotes(array $votes): PlantnetOccurrence
    {
        $this->votes = $votes;
        return $this;
    }
}
