<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Symfony\Component\Console\Output\OutputInterface;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;

class ConsoleCommissionFeesWriter implements CommissionFeesWriterInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write(OperationCollection $operations): void
    {
        $operations->sortByIndex();
        foreach ($operations as $operation) {
            $this->output->writeln($operation->getCommissionFee()->formatAmount());
        }
    }
}
