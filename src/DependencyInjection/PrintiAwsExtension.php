<?php

namespace Printi\AwsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class PrintiAwsExtension extends Extension
{

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!empty($config)) {
            if (isset($config['sns'])) {
                $container->setParameter('printi_sns_config', $config['sns']);
            }

            if (isset($config['s3'])) {
                $container->setParameter('printi_s3_config', $config['s3']);
            }
        }
    }
}