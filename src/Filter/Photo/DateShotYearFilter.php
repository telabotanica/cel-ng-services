<?php

namespace App\Filter\Photo;

use ApiPlatform\Core\Api\FilterInterface;

/** 
 * Filters <code>Occurrence</code> resources on the value of the year 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class DateShotYearFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateShotYear'] = [
                'property' => 'dateShotYear',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the year the photo was shot',
                    'name' => 'dateShotYear',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
