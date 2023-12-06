<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return function (ContainerConfigurator $configurator) {
    $configurator->extension('zmkr_cloudflare_turnstile', [
        'captcha' => [
            'sitekey' => '<sitekey>',
            'secret_key' => '<secret_key>',
        ],
    ]);

    $frameworkConfig = [
        'test' => true,
        'secret' => '',
        'http_method_override' => false,
        'validation' => [
            'email_validation_mode' => 'html5'
        ],
        'php_errors' => [
            'log' => true
        ]
    ];

    if (Kernel::VERSION_ID >= 60200) {
        $frameworkConfig['handle_all_throwables'] = true;
    }

    $configurator->extension('framework', $frameworkConfig);
};
