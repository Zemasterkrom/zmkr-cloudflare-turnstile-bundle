<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Test class asserting that Cloudflare Turnstile form field data is consistent and
 */
class CloudflareTurnstileTypeTest extends TypeTestCase
{
    private CloudflareTurnstileType $type;

    const CAPTCHA_SITEKEY = 'sitekey';

    public function setUp(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstileType(self::CAPTCHA_SITEKEY, true));
    }

    private function initializeFormTypeFactory(CloudflareTurnstileType $type): void
    {
        $this->type = $type;
        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                $this->type
            ], [])
        ];
    }

    public function testCaptchaConstraintIsLoaded(): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class);

        $this->assertTrue($form->getConfig()->getOptions()['constraints'] instanceof CloudflareTurnstileCaptcha);
    }

    public function testCaptchaDefaultFormTypeVars(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertSame(self::CAPTCHA_SITEKEY, $formView->vars['sitekey']);
        $this->assertSame('cf-turnstile', $formView->vars['attr']['class']);
        $this->assertEmpty($formView->vars['explicit_js_loader']);
        $this->assertTrue($formView->vars['enabled']);
    }

    public function testCaptchaExplicitRenderingModeFlag(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class, null, [
            'explicit_js_loader' => 'cloudflareTurnstileLoader'
        ])->createView();

        $this->assertSame('cloudflareTurnstileLoader', $formView->vars['explicit_js_loader']);
    }

    public function testCaptchaFormTypeDisabledFlag(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstileType(self::CAPTCHA_SITEKEY, false));
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertFalse($formView->vars['enabled']);
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
    public function testTurnstileClassIsNotDuplicatedIfAlreadyListedInProvidedClasses(string $providedClass, string $expectedClass): void
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
