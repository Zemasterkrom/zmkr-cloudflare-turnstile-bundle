<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Form\Type;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Twig\Environment;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Integration test class checking that the view associated to the Cloudflare Turnstile widget is rendered correctly
 */
class CloudflareTurnstileTypeIntegrationTest extends KernelTestCase
{
    private Environment $twig;
    private FormFactoryInterface $factory;

    public function setUp(): void
    {
        self::bootKernel();

        /** @disregard P1013 Undefined method */
        /** @disregard P1014 Undefined property */
        /** @phpstan-ignore-next-line */
        $this->twig = method_exists($this, 'getContainer') ? static::getContainer()->get(Environment::class) : self::$container->get(Environment::class);
        $this->initializeFormTypeFactory(new CloudflareTurnstileType('', true));
    }

    private function initializeFormTypeFactory(CloudflareTurnstileType $cloudflareTurnstileType): void
    {
        $this->factory = Forms::createFormFactoryBuilder()->addType($cloudflareTurnstileType)->getFormFactory();
    }

    private function renderWidget(array $options = []): string
    {
        $templateOptions = array_replace_recursive(
            $this->factory->create(CloudflareTurnstileType::class)->createView()->vars,
            $options
        );

        return $this->twig->render('@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig', $templateOptions);
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
            'compatibility_mode' => 'recaptcha'
        ];

        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js?compat=recaptcha', $this->renderWidget($options));
    }

    public function testCaptchaLoadingReferenceWithExplicitModeAndReCaptchaMode(): void
    {
        $options = [
            'explicit_js_loader' => 'cloudflareTurnstileLoader',
            'compatibility_mode' => 'recaptcha'
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
        $options = [
            'id' => 'cloudflareTurnstileWidget'
        ];

        $this->assertStringContainsString('<div id="cloudflareTurnstileWidget" data-sitekey="" class="cf-turnstile" data-response-field-name="cf-turnstile-response"></div>', $this->renderWidget($options));
    }

    public function testCaptchaContainerRenderingWithNoAttributes(): void
    {
        $options = [
            'id' => 'cloudflareTurnstileWidget',
            'sitekey' => 'sitekey'
        ];

        $this->assertStringContainsString('<div id="cloudflareTurnstileWidget" data-sitekey="sitekey" class="cf-turnstile" data-response-field-name="cf-turnstile-response"></div>', $this->renderWidget($options));
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

        $this->assertMatchesRegularExpression('#<div id="cloudflareTurnstileWidget" data-sitekey="sitekey" class="cf-turnstile" data-response-field-name="cf-turnstile-response" data-test-attr="test" data-test-attr-two="" data-test-attr-three="data-test-attr-three"></div>#', $this->renderWidget($options));
    }

    public function testCaptchaCloudflareTurnstileScriptIsNotDuplicatedIfRenderedMultipleTimes(): void
    {
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget());
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget());
    }

    /**
     * @dataProvider customOptionsForCheckingResourcesAreNotDuplicated
     */
    public function testCaptchaWidgetHandlerResourcesAreNotDuplicated(array $firstOptions, array $secondOptions): void
    {
        $firstWidgetRenderingNoOption = $this->renderWidget();
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $firstWidgetRenderingNoOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $firstWidgetRenderingNoOption);

        $secondWidgetRenderingRequiredOption = $this->renderWidget($firstOptions);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $secondWidgetRenderingRequiredOption);
        $this->assertStringContainsString('zmkr_cloudflare_turnstile_widget_handler', $secondWidgetRenderingRequiredOption);

        $thirthWidgetRenderingOnloadOption = $this->renderWidget($secondOptions);
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $thirthWidgetRenderingOnloadOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $thirthWidgetRenderingOnloadOption);

        $fourthWidgetRenderingNoOption = $this->renderWidget();
        $this->assertStringNotContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $fourthWidgetRenderingNoOption);
        $this->assertStringNotContainsString('zmkr_cloudflare_turnstile_widget_handler', $fourthWidgetRenderingNoOption);
    }

    public function testCaptchaWidgetRenderingForRequiredOptionPreventsDuplication(): void
    {
        $firstWidgetRenderingNoRequiredOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $firstWidgetRenderingNoRequiredOption);

        $secondWidgetRenderingRequiredOption = $this->renderWidget(['required' => true]);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $secondWidgetRenderingRequiredOption);

        $thirthWidgetRenderingRequiredOption = $this->renderWidget(['required' => true]);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*"' . CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME . '".*)#', $thirthWidgetRenderingRequiredOption);

        $fourthWidgetRenderingNoRequiredOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $fourthWidgetRenderingNoRequiredOption);
    }

    public function testCaptchaWidgetRenderingForOnloadOptionPreventsDuplication(): void
    {
        $firstWidgetRenderingNoOnloaddOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.onload', $firstWidgetRenderingNoOnloaddOption);

        $secondWidgetRenderingOnloadOption = $this->renderWidget(['id' => 'individual_explicit_js_loader_test', 'individual_explicit_js_loader' => 'cloudflareTurnstileLoader']);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.onload(.*individual_explicit_js_loader_test.*cloudflareTurnstileLoader.*)#', $secondWidgetRenderingOnloadOption);

        $thirthWidgetRenderingOnloadOption = $this->renderWidget(['id' => 'individual_explicit_js_loader_test', 'individual_explicit_js_loader' => 'cloudflareTurnstileLoader2']);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.onload(.*individual_explicit_js_loader_test.*cloudflareTurnstileLoader.*)#', $thirthWidgetRenderingOnloadOption);

        $fourthWidgetRenderingNoOnloadOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.onload', $fourthWidgetRenderingNoOnloadOption);
    }

    public function customOptionsForCheckingResourcesAreNotDuplicated(): iterable
    {
        yield [['required' => true], []];
        yield [['required' => true], ['individual_explicit_js_loader' => 'cloudflareTurnstileLoader']];
        yield [['individual_explicit_js_loader' => 'cloudflareTurnstileLoader'], ['required' => true]];
        yield [['individual_explicit_js_loader' => 'cloudflareTurnstileLoader'], []];
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
