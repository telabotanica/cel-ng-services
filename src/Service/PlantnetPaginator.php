<?php

namespace App\Service;

use App\Model\PlantnetOccurrences;

class PlantnetPaginator
{
    /**
     * PlantNet client
     * @var PlantnetService
     */
    private $plantnetService;

    /**
     * Current page content
     * @var PlantnetOccurrences
     */
    private $content = [];

    public function __construct(PlantnetService $plantnetService) {
        $this->plantnetService = $plantnetService;
    }

    /**
     * @return PlantnetOccurrences
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Start paginator and get first page content
     */
    public function start(int $startDate = 0, string $email = '', int $endDate): void
    {
        $this->content = $this->plantnetService->getOccurrences($startDate, $email, $endDate);
    }

    /**
     * Move to next page and load its content, if any
     */
    public function nextPage(): bool
    {
        if ($this->content && $this->content->hasMore()) {
            // get new content
            $this->content = $this->plantnetService->getOccurrencesByNextUrl($this->content->getNextStartDate());

            return true;
        }

        return false;
    }
}
