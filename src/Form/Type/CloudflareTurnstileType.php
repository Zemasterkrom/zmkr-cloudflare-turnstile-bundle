<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Cloudflare Turnstile captcha form integration
 */
class CloudflareTurnstileType extends AbstractType
{
    private string $sitekey;
    private bool $enabled;

    /**
     * TurnstileType constructor
     *
     * @param string $sitekey The Cloudflare Turnstile sitekey for captcha integration
     * @param bool $enabled Flag indicating whether the captcha is enabled
     */
    public function __construct(string $sitekey, bool $enabled)
    {
        $this->sitekey = $sitekey;
        $this->enabled = $enabled;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $resolver->setDefaults([
            'explicit_js_loader' => '',
            'invalid_message' => $cloudflareTurnstileCaptchaConstraint->message,
            'required' => true,
            'mapped' => false,
            'constraints' => $cloudflareTurnstileCaptchaConstraint
        ]);
    }

    /**
     * Injects the required captcha parameters
     *
     * @param FormView $view The form view
     * @param FormInterface $form The form interface
     * @param array<string, mixed> $options An array of form options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['class'] = trim(isset($options['attr']['class']) ? (preg_match("/\bcf-turnstile\b/", $options['attr']['class']) ? $options['attr']['class'] : 'cf-turnstile ' . trim($options['attr']['class'])) : 'cf-turnstile');
        $view->vars['sitekey'] = $this->sitekey;
        $view->vars['explicit_js_loader'] = $options['explicit_js_loader'];
        $view->vars['enabled'] = $this->enabled;
        $view->vars['required'] = $options['required'];
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
