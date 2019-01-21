<?php

namespace App\Filter\Occurrence;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of the year 
 * of their "dateObserved" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class DateObservedYearFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['dateObservedYear'] = [
                'property' => 'dateObservedYear',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the value of the year of their "dateObserved" property ',
                    'name' => 'dateObservedYear',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
