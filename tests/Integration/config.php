<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $configurator->extension('zmkr_cloudflare_turnstile', [
        'captcha' => [
            'sitekey' => '<sitekey>',
            'secret_key' => '<secret_key>',
        ],
    ]);

    $configurator->extension('framework', [
        'test' => true,
        'secret' => '',
        'http_method_override' => false
    ]);
};
