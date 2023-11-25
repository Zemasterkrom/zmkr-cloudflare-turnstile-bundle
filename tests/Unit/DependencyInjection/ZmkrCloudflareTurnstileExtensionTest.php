<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\DependencyInjection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection\ZmkrCloudflareTurnstileExtension;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

/**
 * Extension configuration test class.
 * Checks that container and services injection is properly handled.
 */
class ZmkrCloudflareTurnstileExtensionTest extends TestCase
{
    private ZmkrCloudflareTurnstileExtension $extension;
    private ContainerBuilder|MockObject $containerBuilder;

    private const CAPTCHA_SITEKEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.sitekey';
    private const CAPTCHA_SECRET_KEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.secret_key';
    private const ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure';
    private const HTTP_CLIENT_OPTIONS_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.http_client.options';

    public function setUp(): void
    {
        $this->extension = new ZmkrCloudflareTurnstileExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testLoadingAndServiceRegistration(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);

        $typeDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.type');

        $this->assertSame($typeDefinition->getClass(), CloudflareTurnstileType::class);
        $this->assertSame($typeDefinition->getArgument('$sitekey'), $this->getVariablePlaceholder(self::CAPTCHA_SITEKEY_REFERENCE));

        $validatorDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.validator');

        $this->assertSame($validatorDefinition->getClass(), CloudflareTurnstileCaptchaValidator::class);
        $this->assertSame($validatorDefinition->getArgument('$cloudflareTurnstileErrorManager')->__toString(), 'zmkr_cloudflare_turnstile.services.error_manager');
        $this->assertSame($validatorDefinition->getArgument('$cloudflareTurnstileClient')->__toString(), CloudflareTurnstileClientInterface::class);

        $errorManagerDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.error_manager');

        $this->assertSame($errorManagerDefinition->getClass(), CloudflareTurnstileErrorManager::class);
        $this->assertSame($errorManagerDefinition->getArgument('$throwOnCoreFailure'), $this->getVariablePlaceholder(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));

        $clientDefinition = $this->containerBuilder->getDefinition(CloudflareTurnstileClientInterface::class);

        $this->assertSame($clientDefinition->getClass(), CloudflareTurnstileClient::class);
        $this->assertSame($clientDefinition->getArgument('$secretKey'), $this->getVariablePlaceholder(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertSame($clientDefinition->getArgument('$options'), $this->getVariablePlaceholder(self::HTTP_CLIENT_OPTIONS_REFERENCE));
    }

    public function testTwigFormThemeLoading(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);

        $this->containerBuilder = $this->createPartialMock(ContainerBuilder::class, ['hasExtension']);
        $this->containerBuilder->method('hasExtension')->willReturn(true);

        $this->extension->prepend($this->containerBuilder);

        $this->assertSame(ZmkrCloudflareTurnstileExtension::TWIG_VIEW_FILEPATH, $this->containerBuilder->getExtensionConfig('twig')[0]['form_themes'][0]);
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

        $this->assertSame($this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE), '<sitekey>');
        $this->assertSame($this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE), '<secret_key>');
        $this->assertSame($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE), false);
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

        $this->assertSame($this->containerBuilder->getParameter(self::CAPTCHA_SITEKEY_REFERENCE), '<sitekey>');
        $this->assertSame($this->containerBuilder->getParameter(self::CAPTCHA_SECRET_KEY_REFERENCE), '<secret_key>');
        $this->assertSame($this->containerBuilder->getParameter(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE), true);
    }

    /**
     * Asserts that when no HTTP client option is provided, the loaded definition corresponds to an empty array
     *
     * @dataProvider configsWithNoHttpClientOption
     */
    public function testLoadingWithRequiredConfigAndNoHttpClientOption(array $providedConfigWithNoHttpClientOption): void
    {
        $this->extension->load([$providedConfigWithNoHttpClientOption], $this->containerBuilder);

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

        $this->assertSame($this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.timeout'), 1);
        $this->assertSame($this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options'), $config['http_client']['options']);
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

        $this->assertSame($this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.timeout'), 1);
        $this->assertSame($this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options.max_duration'), 2);
        $this->assertSame($this->containerBuilder->getParameter('zmkr_cloudflare_turnstile.parameters.http_client.options'), $config['http_client']['options']);
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
