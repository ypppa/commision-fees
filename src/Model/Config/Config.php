<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Config;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Config
{
    private string $baseCurrency;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('baseCurrency', new Assert\NotBlank())
            ->addPropertyConstraint('baseCurrency', new Assert\Type(['type' => ['alpha']]))
            ->addPropertyConstraint('baseCurrency', new Assert\Length(3))
        ;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }
}
