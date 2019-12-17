<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Constraint validator dealing with <code>NoDuplicateConstraint</code>
 * instances.
 *
 * @Annotation
 * @package App\Validator
 */
class NoDuplicateConstraintValidator extends ConstraintValidator {

    private $em;

    public function __construct(EntityManagerInterface $em) { 
        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    public function validate($occ, Constraint $constraint) {
        if (!$constraint instanceof NoDuplicateConstraint) {
            throw new UnexpectedTypeException($constraint, NoDuplicateConstraint::class);
        }
       
        $hasDuplicate = $this->em->getRepository('Entity\Occurrence')->hasDuplicate($occ);

        if ( $hasDuplicate ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }

}
