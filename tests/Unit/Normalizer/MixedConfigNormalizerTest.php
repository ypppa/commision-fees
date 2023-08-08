<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Normalizer\MixedConfigNormalizer;

/**
 * @codeCoverageIgnore
 */
class MixedConfigNormalizerTest extends TestCase
{
    private DenormalizationContext|MockObject $context;
    private MixedConfigNormalizer $denormalizer;

    public function setUp(): void
    {
        $this->context = $this->createMock(DenormalizationContext::class);
        $this->denormalizer = new MixedConfigNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     *
     * @param mixed  $input
     * @param Config $expectedResult
     *
     * @return void
     * @throws InvalidDataException
     */
    public function testDenormalize(mixed $input, Config $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->denormalizer->denormalize($input, $this->context)
        );
    }

    public function denormalizeDataProvider(): array
    {
        return [
            'should return Config instance' => [
                'input' => ['base_currency' => 'EUR'],
                'expectedResult' => (new Config())->setBaseCurrency('EUR'),
            ],
        ];
    }

    public function testException(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->denormalizer->denormalize([], $this->context);
    }
}
