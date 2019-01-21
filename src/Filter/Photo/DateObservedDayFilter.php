<?php

namespace App\Filter\Photo;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

/** 
 * Filters <code>Occurrence</code> resources on the value of the day 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class DateObservedDayFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateObservedYear'] = [
                'property' => 'dateObservedYear',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the day of the observation date.',
                    'name' => 'dateObservedYear',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
