<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection\ZmkrCloudflareTurnstileExtension;

/**
 * Extension configuration test class.
 * Checks that container configuration is properly handled.
 */
class ZmkrCloudflareTurnstileExtensionTest extends TestCase
{
    private ZmkrCloudflareTurnstileExtension $extension;
    private ContainerBuilder $containerBuilder;

    private const CAPTCHA_SITEKEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.sitekey';
    private const CAPTCHA_SECRET_KEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.secret_key';
    private const EXPLICIT_JS_LOADER_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.explicit_js_loader';
    private const ENABLED_FLAG_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.enabled';
    private const ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure';

    public function setUp(): void
    {
        $this->extension = new ZmkrCloudflareTurnstileExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testLoadingAndServiceRegistration(): void
    {
        $this->expectNotToPerformAssertions();

        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);
    }

    /**
     * Checks that excluded parameters defined in the bundle loading class are not loaded in the container
     */
    public function testLoadingContainerParametersExclusion(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);

        $this->assertFalse($this->containerBuilder->hasParameter('zmkr_cloudflare_turnstile.parameters.captcha'));
        $this->assertFalse($this->containerBuilder->hasParameter('zmkr_cloudflare_turnstile.parameters.error_manager'));
        $this->assertFalse($this->containerBuilder->hasParameter('zmkr_cloudflare_turnstile.parameters.http_client'));
    }

    public function testLoadingWithValidCaptchaConfig(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);

        $this->assertTrue($this->containerBuilder->getParameter(self::ENABLED_FLAG_REFERENCE));
        $this->assertSame('<sitekey>', $this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE));
        $this->assertSame('<secret_key>', $this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertNull($this->containerBuilder->getParameter(self::EXPLICIT_JS_LOADER_REFERENCE));
        $this->assertFalse($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    public function testLoadingWithRequiredConfigAndExplicitJsLoader(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>',
                'explicit_js_loader' => 'cloudflareTurnstileLoader'
            ]
        ]], $this->containerBuilder);

        $this->assertTrue($this->containerBuilder->getParameter(self::ENABLED_FLAG_REFERENCE));
        $this->assertSame('<sitekey>', $this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE));
        $this->assertSame('<secret_key>', $this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertSame('cloudflareTurnstileLoader', $this->containerBuilder->getParameter(self::EXPLICIT_JS_LOADER_REFERENCE));
        $this->assertFalse($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    public function testLoadingWithRequiredConfigAndDisabledFlag(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>',
                'enabled' => false
            ]
        ]], $this->containerBuilder);

        $this->assertFalse($this->containerBuilder->getParameter(self::ENABLED_FLAG_REFERENCE));
        $this->assertSame('<sitekey>', $this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE));
        $this->assertSame('<secret_key>', $this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertNull($this->containerBuilder->getParameter(self::EXPLICIT_JS_LOADER_REFERENCE));
        $this->assertFalse($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    public function testLoadingWithRequiredConfigAndEnabledFlag(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>',
                'enabled' => true
            ]
        ]], $this->containerBuilder);

        $this->assertTrue($this->containerBuilder->getParameter(self::ENABLED_FLAG_REFERENCE));
        $this->assertSame('<sitekey>', $this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE));
        $this->assertSame('<secret_key>', $this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertNull($this->containerBuilder->getParameter(self::EXPLICIT_JS_LOADER_REFERENCE));
        $this->assertFalse($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    public function testLoadingWithRequiredConfigAndValidErrorManagerConfig(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ],
            'error_manager' => [
                'throw_on_core_failure' => true
            ]
        ]], $this->containerBuilder);

        $this->assertSame('<sitekey>', $this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE));
        $this->assertSame('<secret_key>', $this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertNull($this->containerBuilder->getParameter(self::EXPLICIT_JS_LOADER_REFERENCE));
        $this->assertTrue($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    /**
     * Asserts that when no HTTP client option is provided, the loaded definition corresponds to an empty array
     *
     * @dataProvider configsWithNoHttpClientOption
     */
    public function testLoadingWithRequiredConfigAndNoHttpClientOption(array $providedConfigWithNoHttpClientOption): void
    {
        $this->extension->load([$providedConfigWithNoHttpClientOption], $this->containerBuilder);

        /** @phpstan-ignore-next-line */
        $this->assertCount(0, $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options'));
    }

    public function testLoadingWithRequiredConfigAndSingleHttpClientOption(): void
    {
        $config = [
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ],
            'http_client' => [
                'options' => [
                    'timeout' => 1,
                ]
            ]
        ];

        $this->extension->load([$config], $this->containerBuilder);

        $this->assertSame(1, $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.timeout'));
        $this->assertSame($config['http_client']['options'], $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options'));
    }

    public function testLoadingWithRequiredConfigAndMultipleHttpClientOptions(): void
    {
        $config = [
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
        ];

        $this->extension->load([$config], $this->containerBuilder);

        $this->assertSame(1, $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.timeout'));
        $this->assertSame(2, $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.max_duration'));
        $this->assertSame($config['http_client']['options'], $this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options'));
    }

    public function configsWithNoHttpClientOption(): iterable
    {
        yield [
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>'
                ]
            ]
        ];

        yield [
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>'
                ],
                'http_client' => []
            ]
        ];

        yield [
            [
                'captcha' => [
                    'sitekey' => '<sitekey>',
                    'secret_key' => '<secret_key>'
                ],
                'http_client' => [
                    'options' => []
                ]
            ]
        ];
    }

    public function getVariablePlaceholder(string $variableName): string
    {
        return "%{$variableName}%";
    }
}
