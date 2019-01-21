<?php

namespace App\Filter\Photo;

use ApiPlatform\Core\Api\FilterInterface;

/** 
 * Filters <code>Occurrence</code> resources on the value of the month 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class DateObservedMonthFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateObservedMonth'] = [
                'property' => 'month',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the month of their "dateObserved" property ',
                    'name' => 'dateObservedMonth',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
