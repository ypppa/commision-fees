<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Service\InputDataProvider;

use Ypppa\CommissionFees\Exception\ConfigurationLoadException;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class YamlConfigurationProviderTest extends AbstractTestCase
{
    public function testGetConfig(): void
    {
        $this->container->compile();
        $configurationProvider = $this->container->get('ypppa.commission_fees.yaml_configuration_provider');
        $this->assertEquals(
            (new Config())->setBaseCurrency('EUR'),
            $configurationProvider->getConfig()
        );
    }

    public function testException(): void
    {
        $this->container->setParameter('config.file_path', 'config_exception.yaml');
        $this->container->compile();
        $configurationProvider = $this->container->get('ypppa.commission_fees.yaml_configuration_provider');
        $this->expectException(ConfigurationLoadException::class);
        $configurationProvider->getConfig();
    }
}
