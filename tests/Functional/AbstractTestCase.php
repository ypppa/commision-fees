<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

abstract class AbstractTestCase extends TestCase
{
    protected ContainerBuilder $container;

    public function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__ . '/../../'));
        $loader->load('services.xml');
    }
}
