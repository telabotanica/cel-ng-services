<?php

namespace App\Model;

class PlantnetOccurrences
{
    /**
     * @var PlantnetOccurrence[]
     */
    private $data = null;

    /**
     * @var boolean
     */
    private $hasMore = false;

    /**
     * @var string
     */
    private $next = '';

    /**
     * @var string
     */
    private $nextStartDate = '';

    /**
     * @return PlantnetOccurrence[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param PlantnetOccurrence[] $data
     * @return PlantnetOccurrences
     */
    public function setData(array $data): PlantnetOccurrences
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }

    /**
     * @param bool $hasMore
     * @return PlantnetOccurrences
     */
    public function setHasMore(bool $hasMore): PlantnetOccurrences
    {
        $this->hasMore = $hasMore;
        return $this;
    }

    /**
     * @return string
     */
    public function getNext(): string
    {
        return $this->next;
    }

    /**
     * @param string $next
     * @return PlantnetOccurrences
     */
    public function setNext(string $next): PlantnetOccurrences
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @return string
     */
    public function getNextStartDate(): string
    {
        return $this->nextStartDate;
    }

    /**
     * @param string $nextStartDate
     * @return PlantnetOccurrences
     */
    public function setNextStartDate(string $nextStartDate): PlantnetOccurrences
    {
        $this->nextStartDate = $nextStartDate;
        return $this;
    }
}
