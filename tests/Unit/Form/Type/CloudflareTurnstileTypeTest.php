<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Form\Type;

use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Constraints\NotBlank;
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
        $this->initializeFormTypeFactory(new CloudflareTurnstileType(self::CAPTCHA_SITEKEY, '', true));
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

        $this->assertTrue($form->getConfig()->getOptions()['constraints'][0] instanceof CloudflareTurnstileCaptcha);
    }

    /**
     * @dataProvider invalidCaptchaConstraintsConfigurations
     */
    public function testCaptchaWithInvalidConstraintThrowsException(array $invalidCaptchaConstraintsConfiguration): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(CloudflareTurnstileType::class, null, $invalidCaptchaConstraintsConfiguration);
    }

    public function testCaptchaDefaultFormTypeVars(): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();
        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->assertSame(self::CAPTCHA_SITEKEY, $formView->vars['sitekey']);
        $this->assertSame('cf-turnstile', $formView->vars['attr']['class']);
        $this->assertEmpty($formView->vars['explicit_js_loader']);
        $this->assertTrue($formView->vars['enabled']);
        $this->assertSame($formView->vars['response_field_name'], $cloudflareTurnstileCaptchaConstraint->responseFieldName);
        $this->assertSame($formView->vars['attr']['data-response-field-name'], $cloudflareTurnstileCaptchaConstraint->responseFieldName);
    }

    public function testCaptchaExplicitRenderingModeFlag(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstileType(self::CAPTCHA_SITEKEY, 'cloudflareTurnstileLoader', true));

        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertSame('cloudflareTurnstileLoader', $formView->vars['explicit_js_loader']);
    }

    public function testCaptchaFormTypeDisabledFlag(): void
    {
        $this->initializeFormTypeFactory(new CloudflareTurnstileType(self::CAPTCHA_SITEKEY, '', false));
        $formView = $this->factory->create(CloudflareTurnstileType::class)->createView();

        $this->assertFalse($formView->vars['enabled']);
    }

    /**
     * @dataProvider validCaptchaResponseFieldNameConfigurations
     */
    public function testValidCaptchaResponseFieldNameConfiguration(array $captchaResponseFieldNameConfiguration, string $expectedResponseFieldName): void
    {
        $formView = $this->factory->create(CloudflareTurnstileType::class, null, $captchaResponseFieldNameConfiguration)->createView();

        $this->assertSame($formView->vars['response_field_name'], $expectedResponseFieldName);
        $this->assertSame($formView->vars['attr']['data-response-field-name'], $expectedResponseFieldName);
    }

    /**
     * @dataProvider invalidCaptchaResponseFieldNameConfigurations
     */
    public function testCaptchaInconsistentResponseFieldNameConfigurationThrowsException(array $inconsistentResponseFieldNameCaptchaConfiguration): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->factory->create(CloudflareTurnstileType::class, null, $inconsistentResponseFieldNameCaptchaConfiguration);
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
                'constraints' => null
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

    public function validCaptchaResponseFieldNameConfigurations(): iterable
    {
        yield [
            [],
            CloudflareTurnstileCaptcha::DEFAULT_RESPONSE_FIELD_NAME
        ];

        yield [
            [
                'constraints' => [
                    new CloudflareTurnstileCaptcha(null, 'cf-turnstile-test-response')
                ]
            ],
            'cf-turnstile-test-response'
        ];

        yield [
            [
                'attr' => [
                    'data-response-field-name' => 'cf-turnstile-test-response-2'
                ],
                'constraints' => [
                    new CloudflareTurnstileCaptcha(null, 'cf-turnstile-test-response-2')
                ]
            ],
            'cf-turnstile-test-response-2'
        ];
    }

    public function invalidCaptchaResponseFieldNameConfigurations(): iterable
    {
        yield [
            [
                'attr' => [
                    'data-response-field-name' => 'cf-turnstile-test-response'
                ],
                'constraints' => [
                    new CloudflareTurnstileCaptcha(null, 'cf-turnstile-test-response-2')
                ]
            ]
        ];

        yield [
            [
                'attr' => [
                    // Not allowed since it is different than the default one and a custom constraint is not used
                    'data-response-field-name' => 'cf-turnstile-test-response'
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

    public function formsWithMultipleCaptchas(): iterable
    {
        $this->setUp();

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
        $this->setUp();

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
