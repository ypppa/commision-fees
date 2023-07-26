<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator\Strategy;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\Strategy\ZeroCommissionFeeStrategy;

class ZeroCommissionFeeStrategyTest extends TestCase
{
    public function testCalculateCommissionFee(): void
    {
        $strategy = new ZeroCommissionFeeStrategy();
        $operation = new Operation(
            new DateTimeImmutable(),
            '1',
            'some_different_user_type',
            Operation::OPERATION_TYPE_WITHDRAW,
            new Money('10000', 'USD')
        );
        $this->assertEquals(
            new Money(0, 'USD'),
            $strategy->calculateCommissionFee($operation, null)
        );
    }
}
