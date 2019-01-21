<?php

namespace App\Filter\Occurrence;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use 
App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of the botanic 
 * family.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class TagFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['county'] = [
                'property' => 'tags',
                'required' => false,
                'type' => 'string',
                'swagger' => [
                    'description' => 'The tags associated to the occurrence. ',
                    'name' => 'tags',
                    'type' => "string"
                ],
            ];


        return $description;
    }


}
