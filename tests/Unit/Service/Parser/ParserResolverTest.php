<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Parser;

use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Service\Parser\ParserInterface;
use Ypppa\CommissionFees\Service\Parser\ParserResolver;

/**
 * @codeCoverageIgnore
 */
class ParserResolverTest extends TestCase
{
    /**
     * @dataProvider getParserDataProvider
     *
     * @param array                $parsers
     * @param string               $key
     * @param ParserInterface|null $expectedResult
     *
     * @return void
     */
    public function testGetParser(array $parsers, string $key, ?ParserInterface $expectedResult): void
    {
        $parserResolver = new ParserResolver($parsers);
        $this->assertEquals($expectedResult, $parserResolver->getParser($key));
    }

    public function getParserDataProvider(): array
    {
        $parsers = [
            'operations.csv' => $this->createMock(ParserInterface::class),
            'operations.json' => $this->createMock(ParserInterface::class),
        ];

        return [
            [
                'parsers' => $parsers,
                'key' => 'operations.json',
                'expectedResult' => $this->createMock(ParserInterface::class),
            ],
            [
                'parsers' => $parsers,
                'key' => 'commission_fee_rules.json',
                'expectedResult' => null,
            ],
        ];
    }
}
