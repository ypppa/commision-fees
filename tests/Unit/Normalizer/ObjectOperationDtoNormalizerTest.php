<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Normalizer\ObjectOperationDtoNormalizer;

/**
 * @codeCoverageIgnore
 */
class ObjectOperationDtoNormalizerTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(DenormalizationContext::class);
        $this->denormalizer = new ObjectOperationDtoNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     *
     * @param mixed        $input
     * @param OperationDto $expectedResult
     *
     * @return void
     */
    public function testDenormalize(ObjectWrapper $input, OperationDto $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->denormalizer->denormalize($input, $this->context)
        );
    }

    public function denormalizeDataProvider(): array
    {
        return [
            'should return OperationDto instance' => [
                'input' => new ObjectWrapper((object) [
                    'operation_date' => '2014-12-31',
                    'user_id' => '4',
                    'user_type' => Operation::USER_TYPE_PRIVATE,
                    'operation_type' => Operation::OPERATION_TYPE_WITHDRAW,
                    'amount' => '1200.00',
                    'currency' => 'EUR',
                ]),
                'expectedResult' => new OperationDto(
                    '2014-12-31',
                    '4',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    '1200.00',
                    'EUR'
                ),
            ],
        ];
    }
}
