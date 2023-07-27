<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Paysera\Component\Normalization\CoreDenormalizer;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Ypppa\CommissionFees\Exception\ConfigurationLoadException;
use Ypppa\CommissionFees\Model\Config\Config;

class YamlConfigurationProvider implements ConfigurationProviderInterface
{
    private string $filePath;
    private ?Config $config;
    private CoreDenormalizer $denormalizer;
    private ValidatorInterface $validator;

    public function __construct(CoreDenormalizer $denormalizer, ValidatorInterface $validator, string $filePath)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->filePath = $filePath;
        $this->config = null;
    }

    /**
     * @return void
     * @throws ConfigurationLoadException
     */
    private function load(): void
    {
        try {
            $configuration = Yaml::parseFile($this->filePath);
            $this->config = $this->denormalizer->denormalize($configuration, Config::class);
            $violations = $this->validator->validate($this->config);
            if ($violations->count() > 0) {
                throw new ValidationFailedException($this->config, $violations);
            }
        } catch (Throwable $exception) {
            throw new ConfigurationLoadException($exception);
        }
    }

    public function getConfig(): Config
    {
        if ($this->config === null) {
            $this->load();
        }

        return $this->config;
    }
}
