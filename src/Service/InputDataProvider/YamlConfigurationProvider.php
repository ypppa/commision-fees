<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Evp\Component\Money\Money;

class YamlConfigurationProvider implements ConfigurationProviderInterface, CommonDataProviderInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function load(): void
    {
        // TODO: Implement load() method.
    }

    public function getBaseCurrency(): string
    {
        // TODO: Implement getBaseCurrency() method.

        return '';
    }

    public function getPrivateDepositCommission(): string
    {
        // TODO: Implement getPrivateDepositCommission() method.

        return '';
    }

    public function getBusinessDepositCommission(): string
    {
        // TODO: Implement getBusinessDepositCommission() method.

        return '';
    }

    public function getPrivateFreeWithdrawAmount(): Money
    {
        // TODO: Implement getPrivateFreeWithdrawAmount() method.

        return new Money();
    }

    public function getPrivateFreeWithdrawCount(): int
    {
        // TODO: Implement getPrivateFreeWithdrawCount() method.

        return 0;
    }

    public function getPrivateWithdrawCommission(): string
    {
        // TODO: Implement getPrivateWithdrawCommission() method.

        return '';
    }

    public function getBusinessWithdrawCommission(): string
    {
        // TODO: Implement getBusinessWithdrawCommission() method.

        return '';
    }
}
