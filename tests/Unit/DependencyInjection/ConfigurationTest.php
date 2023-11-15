<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;
use Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection\Configuration;

/**
 * Configuration processor test class.
 * Checks that parameters validation is correctly handled and that an invalid configuration prevents the bundle from processing.
 */
class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    /**
     * @dataProvider validConfigurations
     */
    public function testProcessorConfigurationSucceeds(array $providedConfig, array $expectedProcessedConfig): void
    {
        $processedConfig = $this->processor->processConfiguration($this->configuration, $providedConfig);

        $this->assertSame($processedConfig, $expectedProcessedConfig);
    }

    public function testProcessorWithNoConfigThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [[]]);
    }

    public function testProcessorWithNoCaptchaConfigThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'captcha' => []
        ]]);
    }

    public function testProcessorWithEmptySitekeyThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'captcha' => [
                'sitekey' => '',
                'secret_key' => '<secret_key>'
            ]
        ]]);
    }

    public function testProcessorWithEmptySecretKeyThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => ''
            ]
        ]]);
    }

    public function testProcessorWithInvalidCoreErrorThrowingConfigThrowsException(): void
    {
        $this->expectException(InvalidTypeException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => ''
            ],
            'error_manager' => [
                'throw_on_core_failure' => '<throw_on_core_failure>'
            ]
        ]]);
    }

    public function validConfigurations(): array
    {
        return [
            [ // Basic configuration without error_manager section
                [
                    'zmkr_cloudflare_turnstile' => [
                        'captcha' => [
                            'sitekey' => '<sitekey>',
                            'secret_key' => '<secret_key>'
                        ]
                    ]
                ],
                [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'error_manager' => [
                        'throw_on_core_failure' => false
                    ]
                ]
            ],
            [ // Configuration with core exception throwing enabled
                [
                    'zmkr_cloudflare_turnstile' => [
                        'captcha' => [
                            'sitekey' => '<sitekey>',
                            'secret_key' => '<secret_key>'
                        ],
                        'error_manager' => [
                            'throw_on_core_failure' => true
                        ]
                    ]
                ],
                [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'error_manager' => [
                        'throw_on_core_failure' => true
                    ]
                ]
            ]
        ];
    }
}
