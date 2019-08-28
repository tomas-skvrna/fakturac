<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordEqualityValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\PasswordEquality */
        $data = $this->context->getRoot()->getData();

        if(trim($value) && $data->getPassword() != $value) {
            $this->context->buildViolation($constraint->message)
                ->atPath('password_retype')
                ->addViolation();
        }
    }
}
