<?php

namespace App\Model;

class PlantnetPartner
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $observationId;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getObservationId(): int
    {
        return $this->observationId;
    }

    /**
     * @param int $observationId
     */
    public function setObservationId(int $observationId): void
    {
        $this->observationId = $observationId;
    }
}
