<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Cloudflare Turnstile form integration component
 */
class CloudflareTurnstileType extends AbstractType
{
    /**
     * @see https://developers.cloudflare.com/turnstile/reference/supported-languages/
     * @since 0.1
     * @version 1.1
     */
    const SUPPORTED_LANGUAGES_LOCALES = [
        'auto'  => true,
        'ar'    => true,
        'ar-eg' => true,
        'bg'    => true,
        'bg-bg' => true,
        'cs'    => true,
        'cs-cz' => true,
        'da'    => true,
        'da-dk' => true,
        'de'    => true,
        'de-de' => true,
        'el'    => true,
        'el-gr' => true,
        'en'    => true,
        'en-us' => true,
        'es'    => true,
        'es-es' => true,
        'fa'    => true,
        'fa-ir' => true,
        'fi'    => true,
        'fi-fi' => true,
        'fr'    => true,
        'fr-fr' => true,
        'he'    => true,
        'he-il' => true,
        'hi'    => true,
        'hi-in' => true,
        'hr'    => true,
        'hr-hr' => true,
        'hu'    => true,
        'hu-hu' => true,
        'id'    => true,
        'id-id' => true,
        'it'    => true,
        'it-it' => true,
        'ja'    => true,
        'ja-jp' => true,
        'ko'    => true,
        'ko-kr' => true,
        'lt'    => true,
        'lt-lt' => true,
        'ms'    => true,
        'ms-my' => true,
        'nb'    => true,
        'nb-no' => true,
        'nl'    => true,
        'nl-nl' => true,
        'pl'    => true,
        'pl-pl' => true,
        'pt'    => true,
        'pt-br' => true,
        'ro'    => true,
        'ro-ro' => true,
        'ru'    => true,
        'ru-ru' => true,
        'sk'    => true,
        'sk-sk' => true,
        'sl'    => true,
        'sl-si' => true,
        'sr'    => true,
        'sr-ba' => true,
        'sv'    => true,
        'sv-se' => true,
        'th'    => true,
        'th-th' => true,
        'tl'    => true,
        'tl-ph' => true,
        'tlh'   => true,
        'tr'    => true,
        'tr-tr' => true,
        'uk'    => true,
        'uk-ua' => true,
        'vi'    => true,
        'vi-vn' => true,
        'zh'    => true,
        'zh-cn' => true,
        'zh-tw' => true
    ];

    private CloudflareTurnstilePropertiesManager $propertiesManager;
    private ?RequestStack $requestStack;

    public function __construct(CloudflareTurnstilePropertiesManager $propertiesManager, ?RequestStack $requestStack = null)
    {
        $this->propertiesManager = $propertiesManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Automatically configures and normalizes Cloudflare Turnstile captcha options for easy integration
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->getDefaultAttributes());

        $resolver->setNormalizer('attr', function (Options $options, array $attributes) {
            $attributes = array_merge($this->getDefaultAttributes()['attr'], $attributes);
            $normalizedLocale = null;

            if (isset($attributes['data-response-field-name'])) {
                throw new LogicException('Unable to change the data-response-field-name attribute as it is automatically generated for validation');
            }

            if (isset($attributes['data-language'])) {
                if (!\is_string($attributes['data-language']) && !(\is_object($attributes['data-language']) && method_exists($attributes['data-language'], '__toString'))) {
                    throw new LogicException('The Cloudflare Turnstile captcha language must be represented by a supported language code');
                }

                $normalizedLocale = str_replace('_', '-', strtolower((string) $attributes['data-language']));

                if (!isset(self::SUPPORTED_LANGUAGES_LOCALES[$normalizedLocale])) {
                    throw new InvalidOptionsException(sprintf('The %s locale is not supported by Cloudflare Turnstile or the current bundle version.', $normalizedLocale));
                }
            } else {
                $request = $this->requestStack ? $this->requestStack->getCurrentRequest() : null;
                $normalizedLocale = $request ? str_replace('_', '-', strtolower($request->getLocale())) : null;

                if ($normalizedLocale && !isset(self::SUPPORTED_LANGUAGES_LOCALES[$normalizedLocale])) {
                    @trigger_error(sprintf('The %s locale is not supported by Cloudflare Turnstile or the current bundle version. Switching back to default browser language detection for the Cloudflare Turnstile captcha.', $normalizedLocale), E_USER_NOTICE);
                    $normalizedLocale = null;
                }
            }

            if ($normalizedLocale) {
                $attributes['data-language'] = $normalizedLocale;
            }
            
            if (isset($attributes['class']) && !\is_scalar($attributes['class']) && !(\is_object($attributes['class']) && method_exists($attributes['class'], '__toString'))) {
                throw new LogicException('Cloudflare Turnstile captcha widget class must be stringable');
            }

            $attributes['class'] = isset($attributes['class']) && (string) $attributes['class'] && !\is_bool($attributes['class']) ? (preg_match("/\bcf-turnstile\b/", $attributes['class']) ? $attributes['class'] : 'cf-turnstile ' . $attributes['class']) : 'cf-turnstile';
            $attributes['data-theme'] ??= 'light';
           
            return $attributes;
        });

        $resolver->setNormalizer('individual_explicit_js_loader', function (Options $options, $individualExplicitJsLoader) {
            if (isset($individualExplicitJsLoader) && !\is_scalar($individualExplicitJsLoader) && !(\is_object($individualExplicitJsLoader) && method_exists($individualExplicitJsLoader, '__toString'))) {
                throw new LogicException('Cloudflare Turnstile captcha individual widget explicit JavaScript loader must be stringable');
            }
        });
    }

    /**
     * Builds the view by injecting the required captcha view parameters.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['explicit_js_loader'] = &$this->propertiesManager->getExplicitJsLoader();
        $view->vars['individual_explicit_js_loader'] = $options['individual_explicit_js_loader'];
        $view->vars['compatibility_mode'] = &$this->propertiesManager->getCompatibilityMode();
        $view->vars['enabled'] = &$this->propertiesManager->isEnabled();
        $view->vars['attr']['data-response-field-name'] = $view->vars['full_name'];
    }

    public function getBlockPrefix(): string
    {
        return 'zmkr_cloudflare_turnstile';
    }

    /**
     * Returns the Cloudflare Turnstile captcha input type.
     *
     * Since the returned response token is linked to a hidden field, the captcha is considered hidden,
     * even though the captcha module may appear to the user for validation/verification, depending on provided configuration parameters.
     */
    public function getParent(): string
    {
        return HiddenType::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultAttributes(): array
    {
        return [
            'individual_explicit_js_loader' => null,
            'mapped' => false,
            'attr' => [
                'data-sitekey' => $this->propertiesManager->getSitekey(),
                'data-theme' => 'light',
                'class' => 'cf-turnstile'
            ],
            'constraints' => [
                new CloudflareTurnstileCaptcha()
            ]
        ];
    }
}