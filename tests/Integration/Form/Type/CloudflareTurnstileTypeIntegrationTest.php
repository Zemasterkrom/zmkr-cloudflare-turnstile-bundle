<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Form\Type;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Integration test class checking that the view associated to the Cloudflare Turnstile widget is rendered correctly
 */
class CloudflareTurnstileTypeIntegrationTest extends KernelTestCase
{
    private Environment $twig;

    public function setUp(): void
    {
        self::bootKernel();

        /** @disregard P1013 Undefined method */
        /** @disregard P1014 Undefined property */
        /** @phpstan-ignore-next-line */
        $this->twig = method_exists($this, 'getContainer') ? static::getContainer()->get(Environment::class) : self::$container->get(Environment::class);
    }

    private function renderWidget(array $context = []): string
    {
        return $this->twig->render('@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig', array_merge([
            'enabled' => true,
            'required' => false,
            'id' => '',
            'attr' => [
                'class' => 'cf-turnstile', // Managed and tested within the CloudflareTurnstileType FormType class
            ],
            'sitekey' => '',
            'explicit_js_loader' => '',
            'recaptcha_compatibility_mode_enabled' => false,
            'response_field_name' => CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME
        ], $context));
    }

    public function testCaptchaLoadingReferenceWithImplicitMode(): void
    {
        $options = [
            'explicit_js_loader' => ''
        ];

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget($options));
    }

    public function testCaptchaLoadingReferenceWithExplicitMode(): void
    {
        $options = [
            'explicit_js_loader' => 'cloudflareTurnstileLoader'
        ];

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&amp;onload=cloudflareTurnstileLoader', $this->renderWidget($options));
    }

    public function testCaptchaLoadingReferenceWithReCaptchaMode(): void
    {
        $options = [
            'recaptcha_compatibility_mode_enabled' => true
        ];

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha', $this->renderWidget($options));
    }

    public function testCaptchaLoadingReferenceWithExplicitModeAndReCaptchaMode(): void
    {
        $options = [
            'explicit_js_loader' => 'cloudflareTurnstileLoader',
            'recaptcha_compatibility_mode_enabled' => true
        ];

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&amp;onload=cloudflareTurnstileLoader&amp;compat=recaptcha', $this->renderWidget($options));
    }

    public function testCaptchaIsNotRenderedIfDisabled(): void
    {
        $options = [
            'enabled' => false
        ];

        $this->assertEmpty($this->renderWidget($options));
    }

    public function testCaptchaDefaultRendering(): void
    {
        $this->assertStringContainsString('<div id="" data-sitekey="" class="cf-turnstile"></div>', $this->renderWidget());
    }

    public function testCaptchaContainerRenderingWithNoAttributes(): void
    {
        $options = [
            'id' => 'cloudflareTurnstileWidget',
            'sitekey' => 'sitekey'
        ];

        $this->assertStringContainsString('<div id="cloudflareTurnstileWidget" data-sitekey="sitekey" class="cf-turnstile"></div>', $this->renderWidget($options));
    }

    public function testCaptchaContainerRenderingWithMultipleAttributes(): void
    {
        $options = [
            'id' => 'cloudflareTurnstileWidget',
            'attr' => [
                'class' => 'cf-turnstile',
                'data-test-attr' => 'test',
                'data-test-attr-two' => '',
                'data-test-attr-three' => true,
                'data-test-attr-four' => false
            ],
            'sitekey' => 'sitekey'
        ];

        $this->assertMatchesRegularExpression('#<div id="cloudflareTurnstileWidget" data-sitekey="sitekey" class="cf-turnstile" data-test-attr="test" data-test-attr-two="" data-test-attr-three="data-test-attr-three"></div>#', $this->renderWidget($options));
    }

    public function testCaptchaCloudflareTurnstileScriptIsNotDuplicatedIfRenderedTwice(): void
    {
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget());
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget());
    }

    public function testCaptchaWidgetHandlerScriptIsNotDuplicatedIfRenderedTwice(): void
    {
        $firstWidgetRenderingNoRequiredOption = $this->renderWidget();
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $firstWidgetRenderingNoRequiredOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $firstWidgetRenderingNoRequiredOption);
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $firstWidgetRenderingNoRequiredOption);

        $secondWidgetRenderingRequiredOption = $this->renderWidget(['required' => true]);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $secondWidgetRenderingRequiredOption);
        $this->assertStringContainsString('zmkr_cloudflare_turnstile_widget_handler', $secondWidgetRenderingRequiredOption);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $secondWidgetRenderingRequiredOption);

        $thirthWidgetRenderingRequiredOption = $this->renderWidget(['required' => true]);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $thirthWidgetRenderingRequiredOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $thirthWidgetRenderingRequiredOption);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $thirthWidgetRenderingRequiredOption);

        $fourthWidgetRenderingNoRequiredOption = $this->renderWidget();
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $fourthWidgetRenderingNoRequiredOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $fourthWidgetRenderingNoRequiredOption);
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $fourthWidgetRenderingNoRequiredOption);
    }

    protected static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
