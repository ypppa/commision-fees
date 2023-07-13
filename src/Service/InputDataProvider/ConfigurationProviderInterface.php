<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Evp\Component\Money\Money;

interface ConfigurationProviderInterface
{
    public function getBaseCurrency(): string;

    public function getPrivateDepositCommission(): string;

    public function getBusinessDepositCommission(): string;

    public function getPrivateFreeWithdrawAmount(): Money;

    public function getPrivateFreeWithdrawCount(): int;

    public function getPrivateWithdrawCommission(): string;

    public function getBusinessWithdrawCommission(): string;
}
