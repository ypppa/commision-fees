<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Functional\Service\Parser;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Service\Parser\CommissionRulesParser;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class CommissionRulesParserTest extends AbstractTestCase
{
    private CommissionRulesParser $parser;

    public function setUp(): void
    {
        parent::setUp();
        $this->container->compile();
        $this->parser = $this->container->get('ypppa.commission_fees.commission_rules_parser');
    }

    /**
     * @dataProvider parseDataProvider
     *
     * @param string $filePath
     * @param array  $expectedResult
     *
     * @return void
     * @throws CommissionFeeCalculationFailedException
     */
    public function testParse(string $filePath, array $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            iterator_to_array($this->parser->parse($filePath))
        );
    }

    public function testException(): void
    {
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $generator = $this->parser->parse('');
        $generator->next();
    }

    public function parseDataProvider(): array
    {
        return [
            'parser should return array of CommissionFeeRule' => [
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
}
