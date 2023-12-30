<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Form\Type;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;;

use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * CloudflareTurnstileType test class.
 * Checks that the form type and its options are correctly configured and conform to the properties provided.
 */
class CloudflareTurnstileTypeTest extends TypeTestCase
{
    private CloudflareTurnstilePropertiesManager $propertiesManager;
    private CloudflareTurnstileType $type;

    const CAPTCHA_SITEKEY = 'sitekey';

    public function setUp(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstilePropertiesManager(self::CAPTCHA_SITEKEY, true));
    }

    private function initializeFormTypeFactory(CloudflareTurnstilePropertiesManager $propertiesManager): void
    {
        $this->propertiesManager = $propertiesManager;
        $this->type = new CloudflareTurnstileType($this->propertiesManager);
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

        $this->assertTrue($form->getConfig()->getOptions()['constraints'][0] instanceof CloudflareTurnstileCaptcha);
    }

    /**
     * @dataProvider invalidCaptchaConstraintsConfigurations
     */
    public function testCaptchaWithInvalidConstraintThrowsException(array $invalidCaptchaConstraintsConfiguration): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->factory->create(CloudflareTurnstileType::class, null, $invalidCaptchaConstraintsConfiguration);
    }

    public function testCaptchaDefaultFormTypeVars(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertSame(self::CAPTCHA_SITEKEY, $formView->vars['attr']['data-sitekey']);
        $this->assertSame('cf-turnstile', $formView->vars['attr']['class']);
        $this->assertTrue($formView->vars['enabled']);
        $this->assertEmpty($formView->vars['explicit_js_loader']);
        $this->assertEmpty($formView->vars['compatibility_mode']);
        $this->assertFalse($formView->vars['required']);
        $this->assertNotEmpty($formView->vars['full_name']);
        $this->assertSame($formView->vars['full_name'], $formView->vars['attr']['data-response-field-name']);
    }

    public function testCaptchaExplicitRenderingMode(): void
    {
        $this->propertiesManager->setExplicitJsLoader('cloudflareTurnstileLoader');

        $firstForm = $this->factory->create(CloudflareTurnstileType::class);
        $firstFormView = $firstForm->createView();

        $this->assertSame('cloudflareTurnstileLoader', $firstFormView->vars['explicit_js_loader']);

        $this->propertiesManager->setExplicitJsLoader('cloudflareTurnstileLoader2');
        $firstFormView = $firstForm->createView();

        $this->assertSame('cloudflareTurnstileLoader2', $firstFormView->vars['explicit_js_loader']);

        $secondForm = $this->factory->create(CloudflareTurnstileType::class);
        $secondFormView = $secondForm->createView();

        $this->assertSame('cloudflareTurnstileLoader2', $secondFormView->vars['explicit_js_loader']);
        $this->assertSame('cloudflareTurnstileLoader2', $firstFormView->vars['explicit_js_loader']);

        $this->propertiesManager->setExplicitJsLoader(null);
        $thirthForm = $this->factory->create(CloudflareTurnstileType::class);
        $thirthFormView = $thirthForm->createView();

        $this->assertNull($thirthFormView->vars['explicit_js_loader']);
        $this->assertNull($firstFormView->vars['explicit_js_loader']);
        $this->assertNull($secondFormView->vars['explicit_js_loader']);
    }

    public function testCaptchaCompatibilityMode(): void
    {
        $this->propertiesManager->setCompatibilityMode('recaptcha');

        $firstForm = $this->factory->create(CloudflareTurnstileType::class);
        $firstFormView = $firstForm->createView();

        $this->assertSame('recaptcha', $firstFormView->vars['compatibility_mode']);

        $this->propertiesManager->setCompatibilityMode('hcaptcha');
        $firstFormView = $firstForm->createView();

        $this->assertSame('hcaptcha', $firstFormView->vars['compatibility_mode']);

        $secondForm = $this->factory->create(CloudflareTurnstileType::class);
        $secondFormView = $secondForm->createView();

        $this->assertSame('hcaptcha', $secondFormView->vars['compatibility_mode']);
        $this->assertSame('hcaptcha', $firstFormView->vars['compatibility_mode']);

        $this->propertiesManager->setCompatibilityMode(null);
        $thirthForm = $this->factory->create(CloudflareTurnstileType::class);
        $thirthFormView = $thirthForm->createView();

        $this->assertNull($thirthFormView->vars['compatibility_mode']);
        $this->assertNull($firstFormView->vars['compatibility_mode']);
        $this->assertNull($secondFormView->vars['compatibility_mode']);
    }

    public function testCaptchaFormTypeDisabledFlag(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstilePropertiesManager(self::CAPTCHA_SITEKEY, false));
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertFalse($formView->vars['enabled']);
    }

    /**
     * @dataProvider validLocales
     */
    public function testCaptchaLanguageConfigurationWithValidLocales(string $providedLocale, string $expectedLocale): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'data-language' => $providedLocale
            ]
        ])->createView();

        $this->assertSame($expectedLocale, $formView->vars['attr']['data-language']);
    }

    public function testCaptchaLanguageConfigurationWithValidLocaleAsStringableObject(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'data-language' => new class
                {
                    public function __toString()
                    {
                        return 'EN';
                    }
                }
            ]
        ])->createView();

        $this->assertSame('en', $formView->vars['attr']['data-language']);
    }

    public function testCaptchaLanguageConfigurationWithUnsupportedLocaleThrowsException(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'data-language' => 'unsupported_locale'
            ]
        ]);
    }

    public function testCaptchaLanguageConfigurationWithInvalidLocaleDataThrowsException(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'data-language' => true
            ]
        ]);
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

        $this->assertSame($expectedClass, $form->createView()->vars['attr']['class']);
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

        $this->assertSame($expectedClass, $form->createView()->vars['attr']['class']);
    }

    /**
     * The class attribute must be prepended with cf-turnstile class if provided data is mixed and does not contain cf-turnstile
     *
     * @dataProvider classesWithMixedData
     */
    public function testTurnstileClassWithMixedData($providedClass, string $expectedClass): void
    {
        $form = $this->factory->create(CloudflareTurnstileType::class, null, [
            'attr' => [
                'class' => $providedClass
            ]
        ]);

        $this->assertSame($expectedClass, $form->createView()->vars['attr']['class']);
    }

    public function testCaptchaFormTypeResponseOnSubmitIsCorrect(): void
    {
        $responseToken = '<response_token>';

        $form = $this->factory->create(CloudflareTurnstileType::class);
        $form->submit($responseToken);

        $this->assertSame($responseToken, $form->getData());
        $this->assertTrue($form->isSynchronized());
    }

    /**
     * @dataProvider formsWithMultipleCaptchas
     */
    public function testMultipleCaptchasOnSameFormIsNotAllowed(FormInterface $form): void
    {
        $this->expectException(LogicException::class);

        $form->createView();
    }

    /**
     * @dataProvider formsWithSingleCaptcha
     */
    public function testSingleCaptchaWithOtherFormTypeOnSameFormIsAllowed(FormInterface $form): void
    {
        $this->expectNotToPerformAssertions();

        $form->createView();
    }

    public function invalidCaptchaConstraintsConfigurations(): iterable
    {
        yield [
            [
                'constraints' => []
            ]
        ];

        yield [
            [
                'constraints' => [
                    new NotBlank(),
                    new CloudflareTurnstileCaptcha()
                ]
            ]
        ];
    }

    public function validLocales(): iterable
    {
        yield ['en', 'en'];
        yield ['EN', 'en'];
        yield ['tlh', 'tlh'];
        yield ['TLH', 'tlh'];
        yield ['ar-eg', 'ar-eg'];
        yield ['AR-eg', 'ar-eg'];
        yield ['ar_EG', 'ar-eg'];
    }

    public function classesWithoutTurnstileClass(): iterable
    {
        yield ['', 'cf-turnstile'];
        yield ['test', 'cf-turnstile test'];
        yield ['test test2', 'cf-turnstile test test2'];

        yield ['acf-turnstile', 'cf-turnstile acf-turnstile'];
        yield ['cf-turnstilea', 'cf-turnstile cf-turnstilea'];
        yield ['acf-turnstilea', 'cf-turnstile acf-turnstilea'];

        yield ['test acf-turnstile', 'cf-turnstile test acf-turnstile'];
        yield ['test cf-turnstilea', 'cf-turnstile test cf-turnstilea'];
        yield ['test acf-turnstilea', 'cf-turnstile test acf-turnstilea'];
    }

    public function classesWithTurnstileClass(): iterable
    {
        yield ['cf-turnstile', 'cf-turnstile'];
        yield ['test cf-turnstile', 'test cf-turnstile'];
        yield ['test cf-turnstile test2', 'test cf-turnstile test2'];

        yield ['test acf-turnstile cf-turnstile', 'test acf-turnstile cf-turnstile'];
        yield ['test cf-turnstilea cf-turnstile', 'test cf-turnstilea cf-turnstile'];

        yield ['acf-turnstile cf-turnstile test', 'acf-turnstile cf-turnstile test'];
        yield ['cf-turnstilea cf-turnstile test', 'cf-turnstilea cf-turnstile test'];

        yield ['test acf-turnstile cf-turnstile test2', 'test acf-turnstile cf-turnstile test2'];
        yield ['test cf-turnstilea cf-turnstile test2', 'test cf-turnstilea cf-turnstile test2'];
    }

    public function classesWithMixedData(): iterable
    {
        yield [true, 'cf-turnstile'];
        yield [false, 'cf-turnstile'];
        yield [0, 'cf-turnstile'];
        yield [1, 'cf-turnstile 1'];
        yield [new class
        {
            public function __toString()
            {
                return "";
            }
        }, 'cf-turnstile'];
    }

    public function formsWithMultipleCaptchas(): iterable
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstilePropertiesManager(self::CAPTCHA_SITEKEY, true));

        yield [
            $this->factory->createBuilder()
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->add('test_type', HiddenType::class)
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder(CloudflareTurnstileType::class, null, [
                'compound' => true
            ])->add('turnstile_type', CloudflareTurnstileType::class)->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                )
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                        ->add('test_type', HiddenType::class)
                )
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add(
                            $this->factory->createBuilder()
                                ->add('turnstile_type', CloudflareTurnstileType::class)
                        )
                )
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add(
                            $this->factory->createBuilder()
                                ->add('turnstile_type', CloudflareTurnstileType::class)
                                ->add('test_type', HiddenType::class)
                        )
                )
                ->add('turnstile_type_2', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                        ->add('turnstile_type_2', CloudflareTurnstileType::class)
                )
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                        ->add('test_type', HiddenType::class)
                        ->add('turnstile_type_2', CloudflareTurnstileType::class)
                )
                ->getForm()
        ];
    }

    public function formsWithSingleCaptcha(): iterable
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstilePropertiesManager(self::CAPTCHA_SITEKEY, true));

        yield [
            $this->factory->createBuilder()
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder(CloudflareTurnstileType::class, null, [
                'compound' => true
            ])->add('test_type', HiddenType::class)->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->add('test_type', HiddenType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add('test_type', HiddenType::class)
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                )
                ->add('test_type', HiddenType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('test_type', HiddenType::class)
                )
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                )
                ->add('test_type', HiddenType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add(
                            $this->factory->createBuilder()
                                ->add('turnstile_type', CloudflareTurnstileType::class)
                        )
                )
                ->add('test_type', HiddenType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add(
                            $this->factory->createBuilder()
                                ->add('test_type', HiddenType::class)
                        )
                )
                ->add('turnstile_type', CloudflareTurnstileType::class)
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                        ->add('test_type', HiddenType::class)
                )
                ->getForm()
        ];

        yield [
            $this->factory->createBuilder()
                ->add(
                    $this->factory->createBuilder()
                        ->add('test_type', HiddenType::class)
                        ->add('turnstile_type', CloudflareTurnstileType::class)
                )
                ->getForm()
        ];
    }
}
