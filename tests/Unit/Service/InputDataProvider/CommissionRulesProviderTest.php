<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\InputDataProvider;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Service\InputDataProvider\CommissionRulesProvider;
use Ypppa\CommissionFees\Service\Parser\ParserInterface;

/**
 * @codeCoverageIgnore
 */
class CommissionRulesProviderTest extends TestCase
{
    private CommissionRulesProvider $commissionRulesProvider;
    private MockObject|ParserInterface $parser;

    public function setUp(): void
    {
        $this->parser = $this->createMock(ParserInterface::class);
        $this->commissionRulesProvider = new CommissionRulesProvider($this->parser, '');
    }

    /**
     * @dataProvider getRuleDataProvider
     *
     * @param CommissionFeeRule[] $rules
     * @param Operation           $operation
     * @param CommissionFeeRule   $expectedResult
     *
     * @return void
     */
    public function testGetRule(array $rules, Operation $operation, CommissionFeeRule $expectedResult): void
    {
        $this->parser
            ->method('parse')
            ->willReturnCallBack(fn() => yield from $rules)
        ;

        $this->assertEquals($expectedResult, $this->commissionRulesProvider->getRule($operation));
    }

    public function getRuleDataProvider(): array
    {
        $rules = [
            (new CommissionFeeRule())
                ->setOperationType(Operation::OPERATION_TYPE_DEPOSIT)
                ->setCommission('0'),
            (new CommissionFeeRule())
                ->setUserType(Operation::USER_TYPE_PRIVATE)
                ->setCommission('0.005'),
            (new CommissionFeeRule())
                ->setUserId(['11'])
                ->setCommission('0.003'),
        ];

        return [
            'rule by userId priority' => [
                'rules' => $rules,
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '11',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('100', 'EUR')
                ),
                'expectedResult' => (new CommissionFeeRule())
                    ->setUserId(['11'])
                    ->setCommission('0.003'),
            ],
            'rule by userType priority' => [
                'rules' => $rules,
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '22',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('100', 'EUR')
                ),
                'expectedResult' => (new CommissionFeeRule())
                    ->setUserType(Operation::USER_TYPE_PRIVATE)
                    ->setCommission('0.005'),
            ],
            'rule by operationType' => [
                'rules' => $rules,
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '22',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('100', 'EUR')
                ),
                'expectedResult' => (new CommissionFeeRule())
                    ->setOperationType(Operation::OPERATION_TYPE_DEPOSIT)
                    ->setCommission('0'),
            ],
            'rule not matches' => [
                'rules' => $rules,
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '22',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('100', 'EUR')
                ),
                'expectedResult' => new CommissionFeeRule(),
            ],
            'empty rules array' => [
                'rules' => [],
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '22',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('100', 'EUR')
                ),
                'expectedResult' => new CommissionFeeRule(),
            ],
        ];
    }
}
