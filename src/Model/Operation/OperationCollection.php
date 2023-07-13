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

    public function __construct()
    {
        $this->operationList = [];
    }

    public function add(Operation $operation): void
    {
        $this->operationList[] = $operation;
    }

    public function sort(): void
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

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->operationList);
    }
}
