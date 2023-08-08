<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Normalizer;

use Evp\Component\Money\Money;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\DenormalizationException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Normalizer\ObjectCommissionRuleNormalizer;

/**
 * @codeCoverageIgnore
 */
class ObjectCommissionRuleNormalizerTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(DenormalizationContext::class);
        $this->denormalizer = new ObjectCommissionRuleNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     *
     * @param mixed             $input
     * @param CommissionFeeRule $expectedResult
     *
     * @return void
     * @throws DenormalizationException
     */
    public function testDenormalize(ObjectWrapper $input, CommissionFeeRule $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->denormalizer->denormalize($input, $this->context)
        );
    }

    public function denormalizeDataProvider(): array
    {
        return [
            'should return CommissionFeeRule instance' => [
                'input' => new ObjectWrapper((object) [
                    'user_id' => ['1', '2'],
                    'user_type' => Operation::USER_TYPE_PRIVATE,
                    'operation_type' => Operation::OPERATION_TYPE_WITHDRAW,
                    'free_operations_count_limit' => 3,
                    'free_operations_amount_limit' => (object) [
                        'amount' => '1000',
                        'currency' => 'EUR',
                    ],
                    'commission' => '0.003',
                    'commission_fee_min' => (object) [
                        'amount' => '0.5',
                        'currency' => 'EUR',
                    ],
                    'commission_fee_max' => (object) [
                        'amount' => '10',
                        'currency' => 'EUR',
                    ],
                ]),
                'expectedResult' => (new CommissionFeeRule())
                    ->setUserId(['1', '2'])
                    ->setUserType(Operation::USER_TYPE_PRIVATE)
                    ->setOperationType(Operation::OPERATION_TYPE_WITHDRAW)
                    ->setFreeOperationsCountLimit(3)
                    ->setFreeOperationsAmountLimit(new Money('1000', 'EUR'))
                    ->setCommission('0.003')
                    ->setCommissionFeeMin(new Money('0.5', 'EUR'))
                    ->setCommissionFeeMax(new Money('10', 'EUR')),
            ],
        ];
    }

    public function testException(): void
    {
        $this->expectException(DenormalizationException::class);
        $this->denormalizer->denormalize(new ObjectWrapper((object) []), $this->context);
    }
}
