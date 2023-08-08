<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Normalizer;

use DateTimeImmutable;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\DenormalizationException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Normalizer\ObjectExchangeRatesNormalizer;

/**
 * @codeCoverageIgnore
 */
class ObjectExchangeRatesNormalizerTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(DenormalizationContext::class);
        $this->denormalizer = new ObjectExchangeRatesNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     *
     * @param mixed         $input
     * @param ExchangeRates $expectedResult
     *
     * @return void
     * @throws DenormalizationException
     */
    public function testDenormalize(ObjectWrapper $input, ExchangeRates $expectedResult): void
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
                    'base' => 'EUR',
                    'date' => '2023-08-07',
                    'rates' => (object) [
                        'CLP' => 946.71533,
                        'EUR' => 1,
                    ],
                ]),
                'expectedResult' => (new ExchangeRates())
                    ->setBase('EUR')
                    ->setDate(new DateTimeImmutable('2023-08-07'))
                    ->addRate(new ExchangeRate('CLP', '946.71533'))
                    ->addRate(new ExchangeRate('EUR', '1')),
            ],
        ];
    }

    public function testException(): void
    {
        $this->expectException(DenormalizationException::class);
        $this->denormalizer->denormalize(new ObjectWrapper((object) []), $this->context);
    }
}
