<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Manager;

use DateTimeImmutable;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

class UserHistoryManager
{
    /**
     * @var UserCumulativeOperations[]
     */
    private array $history;

    public function __construct()
    {
        $this->history = [];
    }

    public function get(string $userId, string $currency, DateTimeImmutable $date): UserCumulativeOperations
    {
        $this->deleteOldRecords($date);
        $startOfWeek = $date->modify('Monday this week')->format('YYYY-mm-dd');
        foreach ($this->history as $userCumulativeOperations) {
            if (
                $userCumulativeOperations->getUserId() === $userId
                && $userCumulativeOperations->getStartOfWeek() === $startOfWeek
            ) {
                return $userCumulativeOperations;
            }
        }

        $userCumulativeOperations = new UserCumulativeOperations($userId, $currency, $date);
        $this->history[] = $userCumulativeOperations;

        return $userCumulativeOperations;
    }

    private function deleteOldRecords(DateTimeImmutable $date): void
    {
        $history = [];
        $startOfWeek = $date->modify('Monday this week')->format('YYYY-mm-dd');
        foreach ($this->history as $userCumulativeOperations) {
            if ($userCumulativeOperations->getStartOfWeek() === $startOfWeek) {
                $history[] = $userCumulativeOperations;
            }
        }
        $this->history = $history;
    }
}
