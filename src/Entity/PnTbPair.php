<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class aimed to link local Occurrence info with remote PN Occurrences
 * We need to store:
 *  - corresponding IDs
 *  - PN Occ updated date: if this date is even with remote, then we're already up-to-date
 *
 * @ORM\Entity(repositoryClass="App\Repository\PnTbPairRepository")
 */
class PnTbPair
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Occurrence", inversedBy="", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $occurrence;

    /**
     * @ORM\Column(type="integer")
     */
    private $plantNetOccurrenceId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $plantnetOccurrenceUpdatedAt;

    public function __construct($occurrence, $plantNetOccurrenceId, $plantnetOccurrenceUpdatedAt) {
        $this->setOccurrence($occurrence);
        $this->setPlantNetOccurrenceId($plantNetOccurrenceId);
        $this->setPlantnetOccurrenceUpdatedAt($plantnetOccurrenceUpdatedAt);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOccurrence(): ?Occurrence
    {
        return $this->occurrence;
    }

    public function setOccurrence(Occurrence $occurrence): self
    {
        $this->occurrence = $occurrence;

        return $this;
    }

    public function getPlantNetOccurrenceId(): ?int
    {
        return $this->plantNetOccurrenceId;
    }

    public function setPlantNetOccurrenceId(int $plantNetOccurrenceId): self
    {
        $this->plantNetOccurrenceId = $plantNetOccurrenceId;

        return $this;
    }

    public function getPlantnetOccurrenceUpdatedAt(): ?\DateTimeInterface
    {
        return $this->plantnetOccurrenceUpdatedAt;
    }

    public function setPlantnetOccurrenceUpdatedAt(\DateTimeInterface $plantnetOccurrenceUpdatedAt): self
    {
        $this->plantnetOccurrenceUpdatedAt = $plantnetOccurrenceUpdatedAt;

        return $this;
    }
}
