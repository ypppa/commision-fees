<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Symfony\Component\Console\Output\OutputInterface;
use Ypppa\CommissionFees\Model\Operation\Operation;

class ConsoleCommissionFeesWriter implements CommissionFeesWriterInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write(Operation $operation): void
    {
        $this->output->writeln($operation->getCommissionFee()->formatAmount());
    }
}
