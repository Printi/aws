<?php

namespace Printi\AwsBundle\Services;

use Aws\AwsClient;
use Aws\Exception\AwsException;

/**
 * Class AwsService
 */
abstract class AwsService
{
    const SERVICE_NAME = "Aws";

    /** @var array $s3Config */
    private $globalConfig;

    /** @var array $s3Config */
    private $config;

    public function __construct(array $globalConfig, array $config)
    {
        $this->globalConfig = $globalConfig;
        $this->config       = $config;
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
    private function getClient(string $key): AwsClient
    {
        static $client;

        $clientClass = spritnf(
            "Aws\\%s\\%sClient",
            self::SERVICE_NAME,
            self::SERVICE_NAME
        );

        $resource = $this->getResource($key);
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
    private function getResourceConfig(string $key): array
    {
        if (!is_array($this->config[$key]) || empty($this->config[$key])) {
            throw new AwsException(sprintf(
                "Config for [%s]:[%s] not found.",
                self::SERVICE_NAME,
                $key
            ));
        }

        return $this->config[$key];
    }
}
