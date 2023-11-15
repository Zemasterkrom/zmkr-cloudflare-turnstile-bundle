<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Extension class for the Cloudflare Turnstile Bundle.
 * This class is responsible for loading the properties required for the captcha system.
 */
class ZmkrCloudflareTurnstileExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $this->processContainerConfigurationRecursively($container, $configs, 'zmkr_cloudflare_turnstile.parameters');
    }

    /**
     * Recursively processes and configures container parameters based on nested user configuration.
     *
     * @param ContainerBuilder $container The Symfony service container.
     * @param array $config The configuration array to process.
     * @param string $rootKey The root key for parameter names (used for recursive calls to increase parameter level).
     */

    private function processContainerConfigurationRecursively(ContainerBuilder $container, array $config, string $rootKey = ''): void
    {
        foreach ($config as $key => $value) {
            $parameterName = $rootKey . '.' . $key;

            if (is_array($value)) {
                $this->processContainerConfigurationRecursively($container, $value, $parameterName);
            } else {
                $container->setParameter($parameterName, $value);
            }
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => ['@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig']
            ]);
        } else {
            throw new \InvalidArgumentException('Twig is required by the bundle as Cloudflare Turnstile captcha is dynamically generated');
        }
    }
}
