<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration class for the Cloudflare Turnstile bundle.
 * This class defines the Turnstile bundle required configuration properties.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zmkr_cloudflare_turnstile');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('captcha')->isRequired()
                    ->children()
                        ->scalarNode('sitekey')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secret_key')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('error_manager')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('throw_on_core_failure')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
