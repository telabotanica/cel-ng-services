<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
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
     * @ORM\OneToOne(targetEntity="App\Entity\Occurrence", inversedBy="pnTbPair", cascade={"persist", "remove"})
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
