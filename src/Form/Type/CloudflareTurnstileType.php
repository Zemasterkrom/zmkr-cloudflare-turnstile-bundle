<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Cloudflare Turnstile form integration component
 */
class CloudflareTurnstileType extends AbstractType
{
    /**
     * @see https://developers.cloudflare.com/turnstile/reference/supported-languages/
     */
    const SUPPORTED_LANGUAGES_LOCALES = [
        'auto' => true,
        'ar-eg' => true,
        'ar' => true,
        'de' => true,
        'en' => true,
        'es' => true,
        'fa' => true,
        'fr' => true,
        'id' => true,
        'it' => true,
        'ja' => true,
        'ko' => true,
        'nl' => true,
        'pl' => true,
        'pt' => true,
        'pt-br' => true,
        'ru' => true,
        'tlh' => true,
        'tr' => true,
        'uk' => true,
        'uk-ua' => true,
        'zh' => true,
        'zh-cn' => true,
        'zh-tw' => true,
    ];

    private string $sitekey;
    private string $explicitJsLoader;
    private bool $enabled;

    /**
     * CloudflareTurnstileType constructor
     *
     * @param string $sitekey The Cloudflare Turnstile sitekey for captcha integration
     * @param string $explicitJsLoader If explicit loading is used, the referenced function will be called to load the captcha instead of using the default loading process
     * @param bool $enabled Flag indicating whether the captcha is enabled
     */
    public function __construct(string $sitekey, string $explicitJsLoader, bool $enabled)
    {
        $this->sitekey = $sitekey;
        $this->explicitJsLoader = $explicitJsLoader;
        $this->enabled = $enabled;
    }


    /**
     * Automatically configures and normalizes Cloudflare Turnstile captcha options for easy integration
     *
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'constraints' => [
                new CloudflareTurnstileCaptcha()
            ]
        ]);

        $resolver->setNormalizer('attr', function (Options $options, array $attributes) {
            if (isset($attributes['data-language'])) {
                if (!\is_string($attributes['data-language'])) {
                    throw new InvalidOptionsException('The Cloudflare Turnstile captcha language must be represented by a supported language code');
                }

                $autoConvertedLocale = str_replace('_', '-', strtolower($attributes['data-language']));

                if (!isset(self::SUPPORTED_LANGUAGES_LOCALES[$autoConvertedLocale])) {
                    throw new InvalidOptionsException(sprintf('The %s locale is not supported by Cloudflare Turnstile', $autoConvertedLocale));
                }

                $attributes['data-language'] = $autoConvertedLocale;
            }

            return $attributes;
        });
    }

    /**
     * Cloudflare Turnstile captcha options are checked for consistency during form building
     *
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!isset($options['constraints'][0]) || !$options['constraints'][0] instanceof CloudflareTurnstileCaptcha) {
            throw new InvalidOptionsException('The first constraint of the Cloudflare Turnstile captcha must be an instance of a Cloudflare Turnstile captcha constraint');
        }

        if (isset($options['attr']['data-response-field-name']) && $options['attr']['data-response-field-name'] !== $options['constraints'][0]->responseFieldName) {
            throw new InvalidOptionsException('data-response-field-name attribute must equals the constraint response field name');
        }
    }

    /**
     * Injects the required captcha view parameters.
     * Checks that the form only contains at most one Cloudflare Turnstile captcha.
     *
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $instanceCounter = 0;
        $formStack = [$form->getRoot()];

        if ($form->getRoot()->getConfig()->getType()->getInnerType() instanceof self) {
            $instanceCounter++;
        }

        while (!empty($formStack)) {
            $currentForm = array_pop($formStack);

            foreach ($currentForm->all() as $childForm) {
                if ($childForm->getConfig()->getType()->getInnerType() instanceof self) {
                    $instanceCounter++;
                }

                if ($instanceCounter > 1) break;

                $formStack[] = $childForm;
            }

            if ($instanceCounter > 1) break;
        }

        if ($instanceCounter > 1) {
            throw new LogicException('Unable to add multiple Cloudflare Turnstile captchas to the same form');
        }

        $view->vars['attr']['class'] = trim(isset($options['attr']['class']) ? (preg_match("/\bcf-turnstile\b/", $options['attr']['class']) ? $options['attr']['class'] : 'cf-turnstile ' . trim($options['attr']['class'])) : 'cf-turnstile');
        $view->vars['sitekey'] = $this->sitekey;
        $view->vars['explicit_js_loader'] = $this->explicitJsLoader;
        $view->vars['enabled'] = $this->enabled;
        $view->vars['required'] = $options['required'];

        /** @var CloudflareTurnstileCaptcha */
        $cloudflareTurnstileCaptchaConstraint = $options['constraints'][0];
        $view->vars['response_field_name'] = $cloudflareTurnstileCaptchaConstraint->responseFieldName;
        $view->vars['attr']['data-response-field-name'] = $cloudflareTurnstileCaptchaConstraint->responseFieldName;
    }

    public function getBlockPrefix(): string
    {
        return 'zmkr_cloudflare_turnstile';
    }

    /**
     * Returns the Turnstile captcha input type.
     *
     * Since the returned input data is linked to a hidden field, the captcha is considered hidden,
     * even though the captcha module may appear to the user for validation/verification, depending on the configuration parameters.
     *
     * @return string Turnstile captcha input type
     */
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
