#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;

$application = new Application('commission-fees', '1.0.0');
$command = new CalculateCommissionFeesCommand();

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
