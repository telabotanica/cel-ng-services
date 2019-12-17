<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint stating that duplicated occurrences are not allowed (same 
 * locality/coordinates, same species, same day of observation)
 *
 * @Annotation
 * @package App\Validator
 */
class NoDuplicateConstraint extends Constraint {
    public $message = 'Duplicated occurrences are not allowed (same locality/coordinates, same species, same day of observation).';

    /**
     * @inheritdoc
     */
    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
