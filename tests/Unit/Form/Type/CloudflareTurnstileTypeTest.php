<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Form\Type;

use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Class that tests Cloudflare Turnstile form field and its rendering.
 */
class CloudflareTurnstileTypeTest extends TypeTestCase
{
    private FormRenderer $formRenderer;

    const CAPTCHA_SITEKEY = 'sitekey';

    public function setUp(): void
    {
        parent::setUp();

        $this->formRenderer = new FormRenderer(new TwigRendererEngine([
            'zmkr_cloudflare_turnstile_widget.html.twig'
        ], new Environment(
            new FilesystemLoader([
                __DIR__ . '/../../../../src/Resources/views'
            ])
        )));
    }

    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                new CloudflareTurnstileType(self::CAPTCHA_SITEKEY)
            ], []),
        ];
    }

    public function testCaptchaConstraintIsLoaded(): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class);

        $this->assertTrue($form->getConfig()->getOptions()['constraints'] instanceof CloudflareTurnstileCaptcha);
    }

    public function testCaptchaFormTypeVarsAreCorrect(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertSame(self::CAPTCHA_SITEKEY, $formView->vars['sitekey']);
        $this->assertSame('cf-turnstile', $formView->vars['attr']['class']);
    }

    /**
     * The class attribute must be prepended with cf-turnstile class since it is not present in the provided classes.
     *
     * @dataProvider classesWithoutTurnstileClass
     */
    public function testTurnstileClassIsPresentIfNotListedInProvidedClasses(string $providedClass, string $expectedClass): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'class' => $providedClass
            ]
        ]);

        $this->assertSame($form->createView()->vars['attr']['class'], $expectedClass);
    }

    /**
     * The class attribute should not be duplicated if the provided classes already contains the cf-turnstile class.
     *
     * @dataProvider classesWithTurnstileClass
     */
    public function testTurnstileClassIsNotDuplicatedIfListedInProvidedClasses(string $providedClass, string $expectedClass): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'class' => $providedClass
            ]
        ]);

        $this->assertSame($form->createView()->vars['attr']['class'], $expectedClass);
    }

    public function testCaptchaFormTypeResponseOnSubmitIsCorrect(): void
    {
        $responseToken = '<response_token>';

        $form = $this->factory->create(CloudflareTurnstileType::class);
        $form->submit($responseToken);

        $this->assertSame($responseToken, $form->getData());
        $this->assertTrue($form->isSynchronized());
    }

    public function testCaptchaFormTypeRenderingWithNoAttributes(): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class);

        $this->assertMatchesRegularExpression('#<div class="cf-turnstile" data-sitekey="sitekey"></div>#', $this->formRenderer->searchAndRenderBlock($form->createView(), 'widget'));
    }

    public function testCaptchaRenderingWithAddedClasses(): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'class' => '  test test2  '
            ]
        ]);

        $this->assertMatchesRegularExpression('#<div class="cf-turnstile test test2" data-sitekey="sitekey"></div>#', $this->formRenderer->searchAndRenderBlock($form->createView(), 'widget'));
    }

    public function testCaptchaRenderingWithMultipleAttributes(): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'class' => '  test test2  ',
                'title' => 'Cloudflare Turnstile Captcha',
                'id' => 'turnstile'
            ]
        ]);

        $this->assertMatchesRegularExpression('#<div class="cf-turnstile test test2" title="Cloudflare Turnstile Captcha" id="turnstile" data-sitekey="sitekey"></div>#', $this->formRenderer->searchAndRenderBlock($form->createView(), 'widget'));
    }

    public function classesWithoutTurnstileClass(): iterable
    {
        yield ['', 'cf-turnstile'];
        yield ['test', 'cf-turnstile test'];
        yield ['test test2', 'cf-turnstile test test2'];

        yield ['  test  ', 'cf-turnstile test'];
        yield ['  test test2  ', 'cf-turnstile test test2'];
        yield ['  test test2 acf-turnstile  ', 'cf-turnstile test test2 acf-turnstile'];
        yield ['  test test2 cf-turnstilea  ', 'cf-turnstile test test2 cf-turnstilea'];
        yield ['  test test2 acf-turnstilea  ', 'cf-turnstile test test2 acf-turnstilea'];

        yield ['acf-turnstile', 'cf-turnstile acf-turnstile'];
        yield ['cf-turnstilea', 'cf-turnstile cf-turnstilea'];
        yield ['acf-turnstilea', 'cf-turnstile acf-turnstilea'];

        yield ['  acf-turnstile  ', 'cf-turnstile acf-turnstile'];
        yield ['  cf-turnstilea  ', 'cf-turnstile cf-turnstilea'];
        yield ['  acf-turnstilea  ', 'cf-turnstile acf-turnstilea'];

        yield ['test acf-turnstile', 'cf-turnstile test acf-turnstile'];
        yield ['test cf-turnstilea', 'cf-turnstile test cf-turnstilea'];
        yield ['test acf-turnstilea', 'cf-turnstile test acf-turnstilea'];

        yield ['  test acf-turnstile  ', 'cf-turnstile test acf-turnstile'];
        yield ['  test cf-turnstilea  ', 'cf-turnstile test cf-turnstilea'];
        yield ['  test acf-turnstilea  ', 'cf-turnstile test acf-turnstilea'];

        yield ['  acf-turnstile test  ', 'cf-turnstile acf-turnstile test'];
        yield ['  cf-turnstilea test  ', 'cf-turnstile cf-turnstilea test'];
        yield ['  acf-turnstilea test  ', 'cf-turnstile acf-turnstilea test'];
    }

    public function classesWithTurnstileClass(): iterable
    {
        yield ['cf-turnstile', 'cf-turnstile'];
        yield ['  cf-turnstile  ', 'cf-turnstile'];

        yield ['test cf-turnstile', 'test cf-turnstile'];
        yield ['  test cf-turnstile  ', 'test cf-turnstile'];

        yield ['test cf-turnstile test2', 'test cf-turnstile test2'];
        yield ['  test cf-turnstile test2  ', 'test cf-turnstile test2'];

        yield ['test acf-turnstile cf-turnstile', 'test acf-turnstile cf-turnstile'];
        yield ['test cf-turnstilea cf-turnstile', 'test cf-turnstilea cf-turnstile'];
        yield ['  test acf-turnstilea cf-turnstile  ', 'test acf-turnstilea cf-turnstile'];

        yield ['acf-turnstile cf-turnstile test', 'acf-turnstile cf-turnstile test'];
        yield ['cf-turnstilea cf-turnstile test', 'cf-turnstilea cf-turnstile test'];
        yield ['  acf-turnstilea cf-turnstile test  ', 'acf-turnstilea cf-turnstile test'];

        yield ['test acf-turnstile cf-turnstile test2', 'test acf-turnstile cf-turnstile test2'];
        yield ['test cf-turnstilea cf-turnstile test2', 'test cf-turnstilea cf-turnstile test2'];
        yield ['  test acf-turnstilea cf-turnstile test2  ', 'test acf-turnstilea cf-turnstile test2'];
    }
}
