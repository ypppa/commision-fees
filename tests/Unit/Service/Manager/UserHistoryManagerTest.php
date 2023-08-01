<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Manager;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\Manager\UserHistoryManager;

/**
 * @codeCoverageIgnore
 */
class UserHistoryManagerTest extends TestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @param array                    $history
     * @param array                    $arguments
     * @param UserCumulativeOperations $expectedResult
     *
     * @return void
     */
    public function testGet(array $history, array $arguments, UserCumulativeOperations $expectedResult): void
    {
        $userHistoryManager = new UserHistoryManager();
        $historyProperty = new ReflectionProperty(UserHistoryManager::class, 'history');
        $historyProperty->setAccessible(true);
        $historyProperty->setValue($userHistoryManager, $history);
        $userCumulativeOperations = $userHistoryManager->get(...$arguments);
        $this->assertEquals($expectedResult, $userCumulativeOperations);
    }

    public function getDataProvider(): array
    {
        return [
            'retrieving instance from history' => [
                'history' => [
                    (new UserCumulativeOperations(
                        '11',
                        'EUR',
                        new DateTimeImmutable('2023-08-02')
                    ))->add(new Money('200', 'EUR')),
                    (new UserCumulativeOperations(
                        '1',
                        'EUR',
                        new DateTimeImmutable('2023-08-01')
                    ))->add(new Money('1000', 'EUR')),
                ],
                'arguments' => ['1', 'EUR', new DateTimeImmutable('2023-08-01')],
                'expectedResult' => (new UserCumulativeOperations(
                    '1',
                    'EUR',
                    new DateTimeImmutable('2023-08-01')
                ))->add(new Money('1000', 'EUR')),
            ],
            'creating new instance if not found' => [
                'history' => [],
                'arguments' => ['1', 'EUR', new DateTimeImmutable('2023-08-01')],
                'expectedResult' => new UserCumulativeOperations(
                    '1',
                    'EUR',
                    new DateTimeImmutable('2023-08-01')
                ),
            ],
        ];
    }
}
