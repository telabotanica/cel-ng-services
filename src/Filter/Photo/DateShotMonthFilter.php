<?php

namespace App\Filter\Photo;

use ApiPlatform\Core\Api\FilterInterface;

/** 
 * Filters <code>Occurrence</code> resources on the value of the month 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class DateShotMonthFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateShotMonth'] = [
                'property' => 'dateShotMonth',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the month of their "dateObserved" property the photo was shot.',
                    'name' => 'dateShotMonth',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
