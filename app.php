#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

$application = new Application('commission-fees', '1.0.0');

$container = new ContainerBuilder();
$loader = new XmlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.xml');

$command = $container->get('ypppa.commission_fees.command.calculate_commission_fees');

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
