<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Model\Operation\OperationCollection;
use Ypppa\CommissionFees\Service\Parser\OperationsParserInterface;

class OperationsDataProvider implements OperationsDataProviderInterface
{
    private OperationsParserInterface $parser;
    private ?OperationCollection $operations;

    public function __construct(OperationsParserInterface $parser)
    {
        $this->parser = $parser;
        $this->operations = null;
    }

    public function getOperations(): OperationCollection
    {
        if ($this->operations === null) {
            $this->load();
        }

        return $this->operations;
    }

    private function load(): void
    {
        $this->operations = $this->parser->parse();
        $this->operations->sortByUserIdAndDate();
    }
}
