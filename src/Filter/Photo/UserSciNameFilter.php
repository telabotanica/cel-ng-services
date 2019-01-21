<?php

namespace App\Filter\Photo;

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
class UserSciNameFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['userSciName'] = [
                'property' => 'userSciName',
                'required' => false,
                'type' => 'string',
                'swagger' => [
                    'description' => 'Filter on the value of the scientific name the observer attributed.',
                    'name' => 'userSciName',
                    'required' => false,
                    'type' => "string"
                ],
            ];


        return $description;
    }


}
