<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('zmkr_cloudflare_turnstile.services.type', CloudflareTurnstileType::class)
        ->tag('form.type')
        ->arg('$sitekey', '%zmkr_cloudflare_turnstile.parameters.captcha.sitekey%');

    $services->alias(CloudflareTurnstileType::class, 'zmkr_cloudflare_turnstile.services.type');

    $services->set('zmkr_cloudflare_turnstile.services.validator', CloudflareTurnstileCaptchaValidator::class)
        ->tag('validator.constraint_validator')
        ->arg('$secretKey', '%zmkr_cloudflare_turnstile.parameters.captcha.secret_key%')
        ->arg('$cloudflareTurnstileErrorManager', new ReferenceConfigurator('zmkr_cloudflare_turnstile.services.error_manager'));

    $services->alias(CloudflareTurnstileCaptchaValidator::class, 'zmkr_cloudflare_turnstile.parameters.services.type');

    $services->set('zmkr_cloudflare_turnstile.services.error_manager', CloudflareTurnstileErrorManager::class)
        ->arg('$throwOnCoreFailure', '%zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure%');

    $services->alias(CloudflareTurnstileErrorManager::class, 'zmkr_cloudflare_turnstile.services.type');
};
