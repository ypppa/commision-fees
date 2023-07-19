<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Validator;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetadataValidatorFactory implements ValidatorFactoryInterface
{
    public function createValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator()
        ;
    }
}
