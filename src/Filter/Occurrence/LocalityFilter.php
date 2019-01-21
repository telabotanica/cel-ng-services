<?php

namespace App\Filter\Occurrence;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of the day 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class LocalityFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['locality'] = [
                'property' => 'locality',
                'required' => false,
                'type' => 'string',
                'swagger' => [
                    'description' => 'Filter on the locality the observation took place in.',
                    'name' => 'locality',
                    'required' => false,
                    'type' => "string"
                ],
            ];


        return $description;
    }


}
