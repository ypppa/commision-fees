<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Reader;

use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Service\Reader\CsvReader;

/**
 * @codeCoverageIgnore
 */
class CsvReaderTest extends TestCase
{
    private CsvReader $reader;

    public function setUp(): void
    {
        $this->reader = new CsvReader();
    }

    /**
     * @dataProvider readDataProvider
     *
     * @param array $expectedResult
     *
     * @return void
     * @throws ReaderException
     */
    public function testRead(array $expectedResult): void
    {
        $operationsGenerator = $this->reader->read('tests/_data/test_csv_reader.csv');
        $this->assertEquals($expectedResult, iterator_to_array($operationsGenerator));
    }

    public function readDataProvider(): array
    {
        return [
            'reader reads csv data as array' => [
                'expectedResult' => [
                    ['2014-12-31', '4', 'private', 'withdraw', '1200.00', 'EUR'],
                    ['2015-01-01', '4', 'private', 'withdraw', '1000.00', 'EUR'],
                ],
            ],
        ];
    }

    public function testException(): void
    {
        $this->expectException(ReaderException::class);
        $operationsGenerator = $this->reader->read('tests/_data/missing_file.csv');
        $operationsGenerator->next();
    }
}
