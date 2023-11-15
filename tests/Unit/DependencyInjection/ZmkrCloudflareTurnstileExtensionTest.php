<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zemasterkrom\CloudflareTurnstileBundle\DependencyInjection\ZmkrCloudflareTurnstileExtension;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

/**
 * Extension configuration test class.
 * Checks that container and services injection is properly handled.
 */
class ZmkrCloudflareTurnstileExtensionTest extends TestCase
{
    private ZmkrCloudflareTurnstileExtension $extension;
    private ContainerBuilder $containerBuilder;

    private const CAPTCHA_SITEKEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.sitekey';
    private const CAPTCHA_SECRET_KEY_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.captcha.secret_key';
    private const ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE = 'zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure';

    public function setUp(): void
    {
        $this->extension = new ZmkrCloudflareTurnstileExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testLoadRegisterServices(): void
    {
        $this->extension->load([[
            'captcha' => [
                'sitekey' => '<sitekey>',
                'secret_key' => '<secret_key>'
            ]
        ]], $this->containerBuilder);

        $typeServiceDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.type');

        $this->assertSame($typeServiceDefinition->getClass(), CloudflareTurnstileType::class);
        $this->assertSame($typeServiceDefinition->getArgument('$sitekey'), $this->getVariablePlaceholder(self::CAPTCHA_SITEKEY_REFERENCE));

        $validatorServiceDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.validator');

        $this->assertSame($validatorServiceDefinition->getClass(), CloudflareTurnstileCaptchaValidator::class);
        $this->assertSame($validatorServiceDefinition->getArgument('$secretKey'), $this->getVariablePlaceholder(self::CAPTCHA_SECRET_KEY_REFERENCE));
        $this->assertSame($validatorServiceDefinition->getArgument('$cloudflareTurnstileErrorManager')->__toString(), 'zmkr_cloudflare_turnstile.services.error_manager');

        $errorManagerServiceDefinition = $this->containerBuilder->getDefinition('zmkr_cloudflare_turnstile.services.error_manager');

        $this->assertSame($errorManagerServiceDefinition->getClass(), CloudflareTurnstileErrorManager::class);
        $this->assertSame($errorManagerServiceDefinition->getArgument('$throwOnCoreFailure'), $this->getVariablePlaceholder(self::ERROR_MANAGER_CORE_ERROR_THROWING_REFERENCE));
    }

    public function testLoadWithValidCaptchaConfigSucceeds(): void
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

    public function testLoadWithRequiredConfigAndValidErrorManagerConfigSucceeds(): void
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

    public function getVariablePlaceholder(string $variableName): string
    {
        return "%{$variableName}%";
    }
}
