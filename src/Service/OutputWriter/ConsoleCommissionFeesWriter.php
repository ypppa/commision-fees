<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Evp\Component\Money\Money;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommissionFeesWriter implements CommissionFeesWriterInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write(Money $commissionFee): void
    {
        $this->output->writeln($commissionFee->formatAmount());
    }
}
