<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Model\Operation\OperationCollection;

class CsvOperationsDataProvider implements OperationsDataProviderInterface, CommonDataProviderInterface
{
    private OperationCollection $operationCollection;

    public function __construct()
    {
        $this->operationCollection = new OperationCollection();
    }

    public function getOperations(): OperationCollection
    {
        return $this->operationCollection;
    }

    public function load(): void
    {
        // TODO: implement csv file load logic

        $this->operationCollection->sort();
    }
}
