<?php

namespace App\Model;

class PlantnetGeo
{
    /**
     * @var ?float
     */
    private $lat;

    /**
     * @var ?float
     */
    private $lon;

    /**
     * GPS accuracy in meters
     * @var ?float
     */
    private $accuracy;

    /**
     * @var ?string
     */
    private $place;

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     * @return PlantnetGeo
     */
    public function setLat(?float $lat): PlantnetGeo
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLon(): ?float
    {
        return $this->lon;
    }

    /**
     * @param float|null $lon
     * @return PlantnetGeo
     */
    public function setLon(?float $lon): PlantnetGeo
    {
        $this->lon = $lon;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAccuracy(): ?float
    {
        return $this->accuracy;
    }

    /**
     * @param float|null $accuracy
     * @return PlantnetGeo
     */
    public function setAccuracy(?float $accuracy): PlantnetGeo
    {
        $this->accuracy = $accuracy;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }

    /**
     * @param string|null $place
     * @return PlantnetGeo
     */
    public function setPlace(?string $place): PlantnetGeo
    {
        $this->place = $place;
        return $this;
    }
}
