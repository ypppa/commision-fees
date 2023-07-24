<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Operation;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Operation>
 */
class OperationCollection implements IteratorAggregate
{
    /**
     * @var Operation[]
     */
    private array $operationList;
    private int $count;

    public function __construct()
    {
        $this->count = 0;
        $this->operationList = [];
    }

    public function add(Operation $operation): void
    {
        $this->count++;
        $operation->setIndex($this->count);
        $this->operationList[] = $operation;
    }

    public function sortByUserIdAndDate(): void
    {
        usort($this->operationList, function (Operation $operation1, Operation $operation2): int {
            $userIdCompareResult = strcmp($operation1->getUserId(), $operation2->getUserId());
            if ($userIdCompareResult === 0) {
                if ($operation1->getDate() < $operation2->getDate()) {
                    return -1;
                }
                if ($operation1->getDate() > $operation2->getDate()) {
                    return 1;
                }

                return 0;
            }

            return $userIdCompareResult;
        });
    }

    public function sortByIndex(): void
    {
        usort($this->operationList, function (Operation $operation1, Operation $operation2): int {
            return $operation1->getIndex() < $operation2->getIndex() ? -1 : 1;
        });
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->operationList);
    }
}
