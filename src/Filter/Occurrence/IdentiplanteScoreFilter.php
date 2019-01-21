<?php

namespace App\Filter\Occurrence;

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
class IdentiplanteScoreFilter implements FilterInterface {



    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['county'] = [
                'property' => 'identiplanteScore',
                'required' => false,
                'type' => 'int',
                'swagger' => [
                    'description' => 'The score obtained on IdentiPlante. ',
                    'name' => 'identiplanteScore',
                    'type' => "int"
                ],
            ];


        return $description;
    }


}
