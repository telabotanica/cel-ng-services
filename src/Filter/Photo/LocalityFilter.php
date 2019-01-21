<?php

namespace App\Filter\Photo;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;


/** 
 * Filters <code>Occurrence</code> resources on the value of their 
 * "locality" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class LocalityFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['locality'] = [
                'property' => 'locality',
                'required' => false,
                'type' => 'string',
                'swagger' => [
                    'description' => 'Filter on the locality the photo was shot in.',
                    'name' => 'locality',
                    'required' => false,
                    'type' => "string"
                ],
            ];


        return $description;
    }


}
