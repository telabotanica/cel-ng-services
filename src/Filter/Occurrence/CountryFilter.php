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
class CountryFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['country'] = [
                'property' => 'country',
                'required' => false,
                'type' => 'string',
                'swagger' => [
                    'description' => 'Filter on the value of the country the observation took place in.',
                    'name' => 'country',
                    'required' => false,
                    'type' => "string"
                ],
            ];


        return $description;
    }


}
