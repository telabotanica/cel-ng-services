<?php

namespace App\Filter\Photo;

use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Api\FilterInterface;
use Elastica\Multi\Search;

use App\Service\OccurrenceSearcherService;
use App\DBAL\CertaintyEnumType;

// @todo use PHP enum for the enum part 
/** 
 * Filters <code>Occurrence</code> resources on the value of their "certainty" property.
 * Only used to hook the filter/parameter in documentation generators 
 * (supported by Swagger and Hydra).
 */
class CertaintyFilter implements FilterInterface {

    public function getDescription(string $resourceClass) : array
    {
        // I override the description to add a buckets array key to put my aggregations
        $description = [];

            $description['certainty'] = [
                'property' => 'certainty',
                'required' => false,
                'type' => 'list',
                'swagger' => [
                    'description' => 'Filter on the "certainty" associated with this photo.',
                    'name' => 'certainty',
                    'required' => false,
                    'type' => "list",
                    'enum' => [CertaintyEnumType::CERTAIN, CertaintyEnumType::DOUBTFUL, CertaintyEnumType::TO_BE_DETERMINED]
                ],
            ];


        return $description;
    }


}
