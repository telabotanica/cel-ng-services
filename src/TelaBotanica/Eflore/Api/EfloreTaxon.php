<?php

namespace App\TelaBotanica\Eflore\Api;

class EfloreTaxon
{
    private $family;
    private $acceptedSciName;
    private $acceptedSciNameId;

    public function getFamily(): string
    {
        return $this->family ?? '';
    }

    /**
     * @param mixed $family
     */
    public function setFamily($family): void
    {
        $this->family = $family;
    }

    public function getAcceptedSciName(): string
    {
        return $this->acceptedSciName ?? '';
    }

    /**
     * @param mixed $acceptedSciName
     */
    public function setAcceptedSciName($acceptedSciName): void
    {
        $this->acceptedSciName = $acceptedSciName;
    }

    /**
     * @return mixed
     */
    public function getAcceptedSciNameId()
    {
        return $this->acceptedSciNameId;
    }

    /**
     * @param mixed $acceptedSciNameId
     */
    public function setAcceptedSciNameId($acceptedSciNameId): void
    {
        $this->acceptedSciNameId = $acceptedSciNameId;
    }
}
