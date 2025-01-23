<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Form\Type;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Twig\Environment;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;
use Zemasterkrom\CloudflareTurnstileBundle\Test\BundleTestingKernel;

/**
 * CloudflareTurnstileType associated widget view test class.
 * Checks that the widget view is properly rendered, notably when using options.
 */
class CloudflareTurnstileTypeViewTest extends KernelTestCase
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
        $this->factory = Forms::createFormFactoryBuilder()->addType(new CloudflareTurnstileType(new CloudflareTurnstilePropertiesManager('', true)))->getFormFactory();
    }

    private function renderWidget(array $options = []): string
    {
        $templateOptions = array_merge(
            $this->factory->create(CloudflareTurnstileType::class)->createView()->vars,
            $options
        );

        return $this->twig->render('@ZmkrCloudflareTurnstile/zmkr_cloudflare_turnstile_widget.html.twig', $templateOptions);
    }

    public function testCaptchaLoadingReferenceWithImplicitMode(): void
    {
        $this->assertStringContainsString('https://challenges.cloudflare.com/turnstile/v0/api.js', $this->renderWidget());
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

    public function testCaptchaContainerDefaultRendering(): void
    {
        $options = [
            'id' => 'form',
            'attr' => [
                'class' => 'cf-turnstile',
                'data-sitekey' => 'sitekey',
                'data-response-field-name' => 'cf-turnstile-response',
                'data-language' => 'auto',
            ]
        ];

        $widgetRendering = $this->renderWidget($options);

        $this->assertMatchesRegularExpression('#<div id="form_cloudflare_turnstile_widget_container" .+></div>#', $widgetRendering);
        $this->assertStringContainsString('class="cf-turnstile"', $widgetRendering);
        $this->assertStringContainsString('data-sitekey="sitekey"', $widgetRendering);
        $this->assertStringContainsString('data-response-field-name="cf-turnstile-response"', $widgetRendering);
        $this->assertStringContainsString('data-language="auto"', $widgetRendering);
    }

    public function testCaptchaRenderingWithFactoryAndCustomAttributes(): void {
        $formVars = $this->factory->create(CloudflareTurnstileType::class, null,  [
            'individual_explicit_js_loader' => 'cloudflareTurnstileLoader',
            'attr' => [
                'class' => 'test-class test-class-two',
                'data-test' => 'test'
            ]
        ])->createView()->vars;

        $widgetRendering = $this->renderWidget($formVars);

        $this->assertMatchesRegularExpression('#<div id="' . $formVars['id'] . '_cloudflare_turnstile_widget_container" .+></div>#', $widgetRendering);
        $this->assertStringContainsString('class="cf-turnstile test-class test-class-two"', $widgetRendering);
        $this->assertStringContainsString('data-sitekey=""', $widgetRendering);
        $this->assertStringContainsString('data-response-field-name="zmkr_cloudflare_turnstile"', $widgetRendering);
        $this->assertStringContainsString('data-test="test"', $widgetRendering);
    }

    public function testCaptchaContainerRenderingWithMultipleAttributes(): void
    {
        $options = [
            'id' => 'form',
            'attr' => [
                'class' => 'cf-turnstile',
                'data-sitekey' => 'sitekey',
                'data-response-field-name' => 'cf-turnstile-response',
                'data-language' => 'auto',
                'data-test-attr' => 'test',
                'data-test-attr-two' => '',
                'data-test-attr-three' => true,
                'data-test-attr-four' => false
            ]
        ];

        $widgetRendering = $this->renderWidget($options);

        $this->assertMatchesRegularExpression('#<div id="form_cloudflare_turnstile_widget_container" .+></div>#', $widgetRendering);
        $this->assertStringContainsString('class="cf-turnstile"', $widgetRendering);
        $this->assertStringContainsString('data-sitekey="sitekey"', $widgetRendering);
        $this->assertStringContainsString('data-response-field-name="cf-turnstile-response"', $widgetRendering);
        $this->assertStringContainsString('data-language="auto"', $widgetRendering);
        $this->assertStringContainsString('data-test-attr="test"', $widgetRendering);
        $this->assertStringContainsString('data-test-attr-two=""', $widgetRendering);
        $this->assertStringContainsString('data-test-attr-three="data-test-attr-three"', $widgetRendering);
        $this->assertStringNotContainsString('data-test-attr-four', $widgetRendering);
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

        $secondWidgetRenderingRequiredOption = $this->renderWidget(['full_name' => 'cloudflare_turnstile_response_field_name', 'required' => true]);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*cloudflare_turnstile_response_field_name.*)#', $secondWidgetRenderingRequiredOption);

        $thirthWidgetRenderingRequiredOption = $this->renderWidget(['full_name' => 'cloudflare_turnstile_response_field_name', 'required' => true]);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.required(.*cloudflare_turnstile_response_field_name.*)#', $thirthWidgetRenderingRequiredOption);

        $fourthWidgetRenderingNoRequiredOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.required', $fourthWidgetRenderingNoRequiredOption);
    }

    public function testCaptchaWidgetRenderingForOnloadOptionPreventsDuplication(): void
    {
        $firstWidgetRenderingNoOnloaddOption = $this->renderWidget();
        $this->assertStringNotContainsString('zmkrCloudflareTurnstileBundleCaptcha.onload', $firstWidgetRenderingNoOnloaddOption);

        $secondWidgetRenderingOnloadOption = $this->renderWidget(['id' => 'form', 'individual_explicit_js_loader' => 'cloudflareTurnstileLoader']);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.onload(.*form_cloudflare_turnstile_widget_container.*cloudflareTurnstileLoader.*)#', $secondWidgetRenderingOnloadOption);

        $thirthWidgetRenderingOnloadOption = $this->renderWidget(['id' => 'form', 'individual_explicit_js_loader' => 'cloudflareTurnstileLoader2']);
        $this->assertMatchesRegularExpression('#zmkrCloudflareTurnstileBundleCaptcha.onload(.*form_cloudflare_turnstile_widget_container.*cloudflareTurnstileLoader2.*)#', $thirthWidgetRenderingOnloadOption);

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
        return BundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
