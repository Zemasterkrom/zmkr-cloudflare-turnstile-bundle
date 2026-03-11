<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@auto' => true,
        '@Symfony' => true,
    ])
    ->setFinder(
        (new Finder())
            // 💡 root folder to check
            ->in(__DIR__)
            ->exclude([
                'var',
                'vendor',
            ])
    )
;
