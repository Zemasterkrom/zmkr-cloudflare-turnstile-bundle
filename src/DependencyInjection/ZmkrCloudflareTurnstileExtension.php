<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Extension class for the Cloudflare Turnstile Bundle.
 * This class is responsible for loading the properties required for the captcha system.
 */
class ZmkrCloudflareTurnstileExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        foreach ($configs as $key => $value) {
            $container->setParameter("zmkr_cloudflare_turnstile.$key", $value);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => ['@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig']
            ]);
        }
    }
}
