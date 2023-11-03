<?php

namespace Zemasterkrom\CfTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Extension class for the Cloudflare Turnstile Bundle.
 * This class is responsible for loading the properties required for the captcha system.
 */
class CfTurnstileExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        foreach ($configs as $key => $value) {
            $container->setParameter("turnstile.$key", $value);
        }
    }
}
