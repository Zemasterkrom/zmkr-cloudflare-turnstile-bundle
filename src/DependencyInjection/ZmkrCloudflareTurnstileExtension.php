<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Extension class for the Cloudflare Turnstile Bundle.
 * Responsible for loading the properties required by the captcha system for bundle services.
 */
class ZmkrCloudflareTurnstileExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $this->processContainerConfiguration($container, $configs, [
            'captcha',
            'error_manager',
            'http_client',
        ], 'zmkr_cloudflare_turnstile.parameters');
    }

    /**
     * Recursively processes and configures container parameters based on nested user configuration
     *
     * @param ContainerBuilder $container The Symfony service container builder
     * @param array<string, mixed> $config The configuration array to process
     * @param array<string> $excludedDefinitions The definitions to exclude from container registration (unnecessary records)
     * @param string $rootKey The root key for parameter names (used for recursive calls to increase parameter level)
     * @param string $keyPath The recursive path to the key-value association from the root key
     */
    private function processContainerConfiguration(ContainerBuilder $container, array $config, array $excludedDefinitions, string $rootKey = '', string $keyPath = ''): void
    {
        foreach ($config as $key => $value) {
            $parameterName = $rootKey . '.' . $key;
            $newKeyPath = $keyPath . ($keyPath ? '.' . $key : $key);

            if (!in_array($newKeyPath, $excludedDefinitions)) {
                $container->setParameter($parameterName, $value);
            }

            if (is_array($value)) {
                $this->processContainerConfiguration($container, $value, $excludedDefinitions, $parameterName, $newKeyPath);
            }
        }
    }

    /**
     * Integrates the Twig view associated to the Cloudflare Turnstile captcha widget
     *
     * @param ContainerBuilder $container Builder of container definitions
     *
     * @throws InvalidConfigurationException If the Twig bundle is not loaded
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => ['@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig']
            ]);
        } else {
            throw new InvalidConfigurationException('Twig is required by the bundle as Cloudflare Turnstile captcha is dynamically generated');
        }
    }
}
