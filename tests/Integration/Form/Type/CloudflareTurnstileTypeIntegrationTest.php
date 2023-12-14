<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Form\Type;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;

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
                'class' => 'cf-turnstile' // Managed and tested within the CloudflareTurnstileType FormType class
            ],
            'sitekey' => '',
            'explicit_js_loader' => ''
        ], $context));
    }

    public function testCaptchaLoadingReferenceWithImplicitMode(): void
    {
        $options = [
            'explicit_js_loader' => ''
        ];

        $this->assertMatchesRegularExpression('#https://challenges.cloudflare.com/turnstile/v0/api.js#', $this->renderWidget($options));
    }

    public function testCaptchaLoadingReferenceWithExplicitMode(): void
    {
        $options = [
            'explicit_js_loader' => 'cloudflareTurnstileLoader'
        ];

        $this->assertMatchesRegularExpression('#https://challenges.cloudflare.com/turnstile/v0/api.js\?onload=cloudflareTurnstileLoader#', $this->renderWidget($options));
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
        $this->assertMatchesRegularExpression('#<div id="" data-sitekey="" class="cf-turnstile"></div>#', $this->renderWidget());
    }

    public function testCaptchaContainerRenderingWithNoAttributes(): void
    {
        $options = [
            'id' => 'cloudflareTurnstileWidget',
            'sitekey' => 'sitekey'
        ];

        $this->assertMatchesRegularExpression('#<div id="cloudflareTurnstileWidget" data-sitekey="sitekey" class="cf-turnstile"></div>#', $this->renderWidget($options));
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
