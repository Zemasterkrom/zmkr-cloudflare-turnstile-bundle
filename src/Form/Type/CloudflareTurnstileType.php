<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
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

    private CloudflareTurnstilePropertiesManager $propertiesManager;

    public function __construct(CloudflareTurnstilePropertiesManager $propertiesManager)
    {
        $this->propertiesManager = $propertiesManager;
    }

    /**
     * Automatically configures and normalizes Cloudflare Turnstile captcha options for easy integration
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'individual_explicit_js_loader' => null,
            'mapped' => false,
            'constraints' => [
                new CloudflareTurnstileCaptcha()
            ]
        ]);

        $resolver->setNormalizer('attr', function (Options $options, array $attributes) {
            if (isset($attributes['data-language'])) {
                if (!\is_string($attributes['data-language']) && !(\is_object($attributes['data-language']) && method_exists($attributes['data-language'], '__toString'))) {
                    throw new InvalidOptionsException('The Cloudflare Turnstile captcha language must be represented by a supported language code');
                }

                $attributes['data-language'] = str_replace('_', '-', strtolower((string) $attributes['data-language']));
            }

            if (isset($attributes['class']) && !\is_scalar($attributes['class']) && !(\is_object($attributes['class']) && method_exists($attributes['class'], '__toString'))) {
                throw new InvalidOptionsException('Cloudflare Turnstile captcha widget class must be stringable');
            }

            $attributes['class'] = isset($attributes['class']) && (string) $attributes['class'] && !\is_bool($attributes['class']) ? (preg_match("/\bcf-turnstile\b/", $attributes['class']) ? $attributes['class'] : 'cf-turnstile ' . $attributes['class']) : 'cf-turnstile';

            return $attributes;
        });
    }

    /**
     * Builds the form.
     *
     * Cloudflare Turnstile captcha options are checked for consistency during form building.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!isset($options['constraints'][0]) || !$options['constraints'][0] instanceof CloudflareTurnstileCaptcha) {
            throw new InvalidConfigurationException('The first constraint of the Cloudflare Turnstile captcha must be an instance of a Cloudflare Turnstile captcha constraint');
        }

        if (isset($options['attr']['data-language']) && !isset(self::SUPPORTED_LANGUAGES_LOCALES[$options['attr']['data-language']])) {
            throw new InvalidOptionsException(sprintf('The %s locale is not supported by Cloudflare Turnstile or current bundle version', $options['attr']['data-language']));
        }

        if (isset($options['individual_explicit_js_loader']) && !\is_scalar($options['individual_explicit_js_loader']) && !(\is_object($options['individual_explicit_js_loader']) && method_exists($options['individual_explicit_js_loader'], '__toString'))) {
            throw new InvalidOptionsException('Cloudflare Turnstile captcha individual widget explicit JavaScript loader must be stringable');
        }
    }

    /**
     * Builds the view by injecting the required captcha view parameters.
     *
     * Checks that the form only contains at most one Cloudflare Turnstile captcha.
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

        $view->vars['explicit_js_loader'] = &$this->propertiesManager->getExplicitJsLoader();
        $view->vars['individual_explicit_js_loader'] = $options['individual_explicit_js_loader'];
        $view->vars['compatibility_mode'] = &$this->propertiesManager->getCompatibilityMode();
        $view->vars['enabled'] = $this->propertiesManager->isEnabled();
        $view->vars['attr']['data-sitekey'] = $this->propertiesManager->getSitekey();
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
}
