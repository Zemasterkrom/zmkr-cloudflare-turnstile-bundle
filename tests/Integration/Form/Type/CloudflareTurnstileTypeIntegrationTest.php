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
        $this->twig = static::getContainer()->get(Environment::class); // @phpstan-ignore-line
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

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js?onload=cloudflareTurnstileLoader', $this->renderWidget($options));
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
        $firstWidgetRenderingWithoutRequiredOption = $this->renderWidget();
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $firstWidgetRenderingWithoutRequiredOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $firstWidgetRenderingWithoutRequiredOption);
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $firstWidgetRenderingWithoutRequiredOption);

        $secondWidgetRendering = $this->renderWidget(['required' => true]);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $secondWidgetRendering);
        $this->assertStringContainsString('zmkr_cloudflare_turnstile_widget_handler', $secondWidgetRendering);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $secondWidgetRendering);

        $thirthWidgetRendering = $this->renderWidget(['required' => true]);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $thirthWidgetRendering);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $thirthWidgetRendering);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $thirthWidgetRendering);

        $fourthWidgetRenderingWithoutRequiredOption = $this->renderWidget();
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $fourthWidgetRenderingWithoutRequiredOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $fourthWidgetRenderingWithoutRequiredOption);
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $fourthWidgetRenderingWithoutRequiredOption);
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
