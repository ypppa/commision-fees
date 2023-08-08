<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Functional\Service\Parser;

use Evp\Component\Money\Money;
use Exception;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class ParserTest extends AbstractTestCase
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
            iterator_to_array($parser->parse($filePath, OperationDto::class))
        );
    }

    /**
     * @dataProvider parseJsonDataProvider
     *
     * @param string $serviceId
     * @param string $className
     * @param string $filePath
     * @param array  $expectedResult
     *
     * @return void
     * @throws Exception
     */
    public function testParseJson(string $serviceId, string $className, string $filePath, array $expectedResult): void
    {
        $parser = $this->container->get($serviceId);
        $this->assertEquals(
            $expectedResult,
            iterator_to_array($parser->parse($filePath, $className))
        );
    }

    public function parseCsvDataProvider(): array
    {
        return [
            'parser should return an array of Operation' => [
                'filePath' => 'tests/_data/test_csv_reader.csv',
                'expectedResult' => [
                    new OperationDto(
                        '2014-12-31',
                        '4',
                        'private',
                        'withdraw',
                        '1200.00',
                        'EUR'
                    ),
                    new OperationDto(
                        '2015-01-01',
                        '4',
                        'private',
                        'withdraw',
                        '1000.00',
                        'EUR'
                    ),
                ],
            ],
        ];
    }

    public function parseJsonDataProvider(): array
    {
        return [
            'parser should return an array of Operation' => [
                'serviceId' => 'ypppa.commission_fees.json_operations_parser',
                'className' => OperationDto::class,
                'filePath' => 'tests/_data/test_json_reader.json',
                'expectedResult' => [
                    new OperationDto(
                        '2014-12-31',
                        '4',
                        'private',
                        'withdraw',
                        '1200.00',
                        'EUR'
                    ),
                    new OperationDto(
                        '2015-01-01',
                        '4',
                        'private',
                        'withdraw',
                        '1000.00',
                        'EUR'
                    ),
                ],
            ],
            'parser should return array of CommissionFeeRule' => [
                'serviceId' => 'ypppa.commission_fees.commission_rules_parser',
                'className' => CommissionFeeRule::class,
                'filePath' => 'tests/_data/test_commission_fee_rules_parser.json',
                'expectedResult' => [
                    (new CommissionFeeRule())
                        ->setOperationType('deposit')
                        ->setCommission('0.0003'),
                    (new CommissionFeeRule())
                        ->setUserType('private')
                        ->setOperationType('withdraw')
                        ->setFreeOperationsCountLimit(3)
                        ->setFreeOperationsAmountLimit(new Money('1000', 'EUR'))
                        ->setCommission('0.003'),
                ],
            ],
        ];
    }

    public function testException(): void
    {
        $parser = $this->container->get('ypppa.commission_fees.csv_operations_parser');
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $generator = $parser->parse('', OperationDto::class);
        $generator->next();
    }
}
