<?php

namespace App\Filter\Occurrence;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of their "isPublic" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class IsPublicFilter implements FilterInterface {

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['isPublic'] = [
                "property" => 'isPublic',
                "required" => false,
                'type' => 'boolean',
                'swagger' => [
                    'description' => 'Filter only public occurrence',
                    'name' => 'isPublic',
                    'type' => 'boolean',
                ],
            ];


        return $description;
    }


}
