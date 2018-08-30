<?php

namespace Printi\AwsBundle\Services;

use Aws\AwsClient;
use Aws\Exception\AwsException;
use OutOfRangeException;

/**
 * Class AwsService
 */
abstract class AwsService
{
    /** @var array $globalConfig */
    private $globalConfig;

    /** @var array $config */
    private $config;

    /** @var string $service */
    private $service;

    public function __construct(array $globalConfig, array $config)
    {
        $this->globalConfig = $globalConfig;
        $this->config       = $config;

        $path          = explode('\\', get_class($this));
        $this->service = array_pop($path);
    }

    /**
     * Returns an instance of the desired service client for the chosen
     * region. If no region is provided, the one defined on the `global`
     * section will be used instead. If no `global` region is set, the
     * default region `us-east-1` will take place.
     *
     * @param string $key The desired resource key
     *
     * @return AwsClient  An instance of the desired AWS client
     */
    protected function getClient(string $key): AwsClient
    {
        static $client;

        $clientClass = sprintf(
            "Aws\\%s\\%sClient",
            $this->service,
            $this->service
        );

        $resource = $this->getResourceConfig($key);
        $region   = $resource['region'] ?? $this->globalConfig['region'];

        $shouldInstanciate = (
            !is_subclass_of($client, $clientClass) ||
            $client->getRegion() !== $region
        );

        if ($shouldInstanciate) {
            $client = new $clientClass([
                "version"     => $this->globalConfig['version'],
                "region"      => $region,
                "credentials" => $this->globalConfig['credentials'],
            ]);
        }

        return $client;
    }

    /**
     * Returns the resource config for the provided key.
     *
     * @param string $key The aws resource key
     *
     * @throws AwsException
     * @return array      The resource config array
     */
    protected function getResourceConfig(string $key): array
    {
        if (!is_array($this->config[$key]) || empty($this->config[$key])) {
            throw new \OutOfRangeException(sprintf(
                "Config for [%s]:[%s] not found.",
                $this->service,
                $key
            ));
        }

        return $this->config[$key];
    }

    /**
     * Returns the config array for this Bundle
     *
     * @return array
     */
    protected function getConfig(): array
    {
        return $this->config;
    }
}
