<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Functional\Service\Parser;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Exception;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class OperationsParserTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->container->compile();
    }

    /**
     * @dataProvider parseCsvDataProvider
     *
     * @param string $filePath
     * @param array  $expectedResult
     *
     * @return void
     * @throws Exception
     */
    public function testParseCsv(string $filePath, array $expectedResult): void
    {
        $parser = $this->container->get('ypppa.commission_fees.csv_operations_parser');
        $this->assertEquals(
            $expectedResult,
            iterator_to_array($parser->parse($filePath))
        );
    }

    /**
     * @dataProvider parseJsonDataProvider
     *
     * @param string $filePath
     * @param array  $expectedResult
     *
     * @return void
     * @throws Exception
     */
    public function testParseJson(string $filePath, array $expectedResult): void
    {
        $parser = $this->container->get('ypppa.commission_fees.json_operations_parser');
        $this->assertEquals(
            $expectedResult,
            iterator_to_array($parser->parse($filePath))
        );
    }

    public function parseCsvDataProvider(): array
    {
        return [
            'parser should return an array of Operation' => [
                'filePath' => 'tests/_data/test_csv_reader.csv',
                'expectedResult' => [
                    new Operation(
                        new DateTimeImmutable('2014-12-31'),
                        '4',
                        'private',
                        'withdraw',
                        new Money('1200.00', 'EUR')
                    ),
                    new Operation(
                        new DateTimeImmutable('2015-01-01'),
                        '4',
                        'private',
                        'withdraw',
                        new Money('1000.00', 'EUR')
                    ),
                ],
            ],
        ];
    }

    public function parseJsonDataProvider(): array
    {
        return [
            'parser should return an array of Operation' => [
                'filePath' => 'tests/_data/test_json_reader.json',
                'expectedResult' => [
                    new Operation(
                        new DateTimeImmutable('2014-12-31'),
                        '4',
                        'private',
                        'withdraw',
                        new Money('1200.00', 'EUR')
                    ),
                    new Operation(
                        new DateTimeImmutable('2015-01-01'),
                        '4',
                        'private',
                        'withdraw',
                        new Money('1000.00', 'EUR')
                    ),
                ],
            ],
        ];
    }

    public function testException(): void
    {
        $parser = $this->container->get('ypppa.commission_fees.csv_operations_parser');
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $generator = $parser->parse('');
        $generator->next();
    }
}
