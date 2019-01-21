<?php

namespace App\Filter\Photo;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of the botanic 
 * family.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class FamilyFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['county'] = [
                'property' => 'family',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'Filter on the name of the botanic family of the occurrence the photos are associated with. ',
                    'name' => 'family',
                    'required' => false,
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
