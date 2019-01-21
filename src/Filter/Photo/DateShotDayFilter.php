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
class DateShotDayFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateShotDay'] = [
                'property' => 'dateShotDay',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the day the photo was shot.',
                    'name' => 'dateShotDay',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
