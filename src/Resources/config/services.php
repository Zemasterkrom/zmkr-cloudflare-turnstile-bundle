<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;
use Zemasterkrom\CloudflareTurnstileBundle\Twig\UniqueMarkupIncluderExtension;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->private();

    $services->set('zmkr_cloudflare_turnstile.services.manager.properties_manager', CloudflareTurnstilePropertiesManager::class)
        ->arg(0, '%zmkr_cloudflare_turnstile.parameters.captcha.sitekey%')
        ->arg(1, '%zmkr_cloudflare_turnstile.parameters.captcha.enabled%')
        ->arg(2, '%zmkr_cloudflare_turnstile.parameters.captcha.explicit_js_loader%')
        ->arg(3, '%zmkr_cloudflare_turnstile.parameters.captcha.compatibility_mode%');

    $services->alias(CloudflareTurnstilePropertiesManager::class, 'zmkr_cloudflare_turnstile.services.manager.properties_manager');

    $services->set('zmkr_cloudflare_turnstile.services.type.type', CloudflareTurnstileType::class)
        ->tag('form.type')
        ->arg(0, new ReferenceConfigurator('zmkr_cloudflare_turnstile.services.manager.properties_manager'));

    $services->set('zmkr_cloudflare_turnstile.services.validator.validator', CloudflareTurnstileCaptchaValidator::class)
        ->tag('validator.constraint_validator')
        ->arg(0, new ReferenceConfigurator(CloudflareTurnstileClientInterface::class))
        ->arg(1, new ReferenceConfigurator('zmkr_cloudflare_turnstile.services.manager.error_manager'))
        ->arg(2, '%zmkr_cloudflare_turnstile.parameters.captcha.enabled%');

    $services->set('zmkr_cloudflare_turnstile.services.manager.error_manager', CloudflareTurnstileErrorManager::class)
        ->arg(0, '%zmkr_cloudflare_turnstile.parameters.error_manager.throw_on_core_failure%');

    $services->set('zmkr_cloudflare_turnstile.services.client.client', CloudflareTurnstileClient::class)
        ->arg(0, new ReferenceConfigurator('http_client'))
        ->arg(1, '%zmkr_cloudflare_turnstile.parameters.captcha.secret_key%')
        ->arg(2, '%zmkr_cloudflare_turnstile.parameters.http_client.options%');

    $services->alias(CloudflareTurnstileClientInterface::class, 'zmkr_cloudflare_turnstile.services.client.client');

    $services->set('zmkr_cloudflare_turnstile.services.twig.unique_markup_includer_extension', UniqueMarkupIncluderExtension::class)
        ->tag('twig.extension');
};
