<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree class for Cloudflare Turnstile bundle.
 * Defines and configures required and optional properties of the bundle.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zmkr_cloudflare_turnstile');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('captcha')
                    ->isRequired()
                    ->children()
                        ->scalarNode('sitekey')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('secret_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('explicit_js_loader')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('compatibility_mode')
                            ->defaultNull()
                        ->end()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('error_manager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('throw_on_core_failure')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('http_client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('options')
                            ->useAttributeAsKey('name')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
