<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\ErrorManager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('zmkr_cloudflare_turnstile.services.type', CloudflareTurnstileType::class)
        ->tag('form.type')
        ->arg('$sitekey', '%zmkr_cloudflare_turnstile.parameters.captcha.sitekey%')
        ->arg('$enabled', '%zmkr_cloudflare_turnstile.parameters.enabled%');

    $services->set('zmkr_cloudflare_turnstile.services.validator', CloudflareTurnstileCaptchaValidator::class)
        ->tag('validator.constraint_validator')
        ->arg('$cloudflareTurnstileErrorManager', new ReferenceConfigurator('zmkr_cloudflare_turnstile.services.error_manager'))
        ->arg('$cloudflareTurnstileClient', new ReferenceConfigurator(CloudflareTurnstileClientInterface::class))
        ->arg('$enabled', '%zmkr_cloudflare_turnstile.parameters.enabled%');

    $services->set('zmkr_cloudflare_turnstile.services.error_manager', CloudflareTurnstileErrorManager::class)
        ->arg('$throwOnCoreFailure', '%zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure%');

    $services->set('zmkr_cloudflare_turnstile.services.client', CloudflareTurnstileClient::class)
        ->arg('$secretKey', '%zmkr_cloudflare_turnstile.parameters.captcha.secret_key%')
        ->arg('$options', '%zmkr_cloudflare_turnstile.parameters.http_client.timeout%');

    $services->set(CloudflareTurnstileClientInterface::class, CloudflareTurnstileClient::class)
        ->arg('$secretKey', '%zmkr_cloudflare_turnstile.parameters.captcha.secret_key%')
        ->arg('$options', '%zmkr_cloudflare_turnstile.parameters.http_client.options%');
};
