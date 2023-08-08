<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Normalizer\MixedOperationDtoNormalizer;

/**
 * @codeCoverageIgnore
 */
class MixedOperationDtoNormalizerTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(DenormalizationContext::class);
        $this->denormalizer = new MixedOperationDtoNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     *
     * @param mixed        $input
     * @param OperationDto $expectedResult
     *
     * @return void
     * @throws InvalidDataException
     */
    public function testDenormalize(mixed $input, OperationDto $expectedResult): void
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
                'input' => [
                    'operation_date' => '2014-12-31',
                    'user_id' => '4',
                    'user_type' => Operation::USER_TYPE_PRIVATE,
                    'operation_type' => Operation::OPERATION_TYPE_WITHDRAW,
                    'amount' => '1200.00',
                    'currency' => 'EUR',
                ],
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

    public function testException(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->denormalizer->denormalize([], $this->context);
    }
}
