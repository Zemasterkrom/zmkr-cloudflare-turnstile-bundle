<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Unit\DependencyInjection;

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
     * This function is used to sort processed configurations by key in order to be independent from the processor interface.
     *
     * @param array<string, mixed> $array Array to sort by key
     */
    private function recursiveKsort(array &$array): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveKsort($value);
            }
        }

        ksort($array);
    }

    /**
     * @dataProvider validConfigurations
     */
    public function testProcessorConfiguration(array $providedConfig, array $expectedProcessedConfig): void
    {
        $processedConfig = $this->processor->processConfiguration($this->configuration, $providedConfig);

        $this->recursiveKsort($processedConfig);
        $this->recursiveKsort($expectedProcessedConfig);

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

    public function testProcessorWithInvalidEnabledFlagThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'enabled' => null
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
                'secret_key' => '<secret_key>'
            ],
            'error_manager' => [
                'throw_on_core_failure' => '<throw_on_core_failure>'
            ]
        ]]);
    }

    public function testProcessorWithInvalidHttpClientOptionsEntry(): void
    {
        $this->expectException(InvalidTypeException::class);

        $this->processor->processConfiguration($this->configuration, [[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ],
            'http_client' => [
                'options' => false
            ]
        ]]);
    }

    public function validConfigurations(): iterable
    {
        // Basic configuration without error_manager section
        yield [
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
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Basic configuration without error_manager section options
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'error_manager' => []
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Configuration with core exception throwing enabled
        yield [
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
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => true
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Configuration without http_client section options
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'http_client' => []
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Configuration with no HTTP option
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'http_client' => [
                        'options' => []
                    ]
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Configuration with single HTTP option
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'http_client' => [
                        'options' => [
                            'timeout' => 1
                        ]
                    ]
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => [
                        'timeout' => 1
                    ]
                ]
            ]
        ];

        // Configuration with multiple HTTP options
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>'
                    ],
                    'http_client' => [
                        'options' => [
                            'timeout' => 1,
                            'max_duration' => 2
                        ]
                    ]
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => [
                        'timeout' => 1,
                        'max_duration' => 2
                    ]
                ]
            ]
        ];

        // Enabled extension
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>',
                        'enabled' => true
                    ]
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => true
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];

        // Disabled extension
        yield [
            [
                'zmkr_cloudflare_turnstile' => [
                    'captcha' => [
                        'sitekey' => '<sitekey>',
                        'secret_key' => '<secret_key>',
                        'enabled' => false
                    ]
                ]
            ],
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>',
                    'enabled' => false
                ],
                'error_manager' => [
                    'throw_on_core_failure' => false
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];
    }
}
