<?php

namespace Printi\AwsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PrintiAwsExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (empty($config)) {
            throw new InvalidConfigurationException(
                "No config found.
                Please, set a `printi_aws` config namespace."
            );
        }

        $configKeys = array_keys($config);
        foreach ($configKeys as $key) {
            $container->setParameter("config_{$key}", $config[$key]);
        }
    }
}
