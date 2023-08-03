<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Unit\Service\Reader;

use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Service\Reader\JsonReader;

/**
 * @codeCoverageIgnore
 */
class JsonReaderTest extends TestCase
{
    private JsonReader $reader;

    public function setUp(): void
    {
        $this->reader = new JsonReader();
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
        $operationsGenerator = $this->reader->read('tests/_data/test_json_reader.json');
        $this->assertEquals($expectedResult, iterator_to_array($operationsGenerator));
    }

    public function readDataProvider(): array
    {
        return [
            'reader reads json data as array of objects' => [
                'expectedResult' => [
                    (object) [
                        'operation_date' => '2014-12-31',
                        'user_id' => '4',
                        'user_type' => 'private',
                        'operation_type' => 'withdraw',
                        'amount' => '1200.00',
                        'currency' => 'EUR',
                    ],
                    (object) [
                        'operation_date' => '2015-01-01',
                        'user_id' => '4',
                        'user_type' => 'private',
                        'operation_type' => 'withdraw',
                        'amount' => '1000.00',
                        'currency' => 'EUR',
                    ],
                ],
            ],
        ];
    }

    public function testException(): void
    {
        $this->expectException(ReaderException::class);
        $operationsGenerator = $this->reader->read('tests/_data/missing_file.json');
        $operationsGenerator->next();
    }
}
