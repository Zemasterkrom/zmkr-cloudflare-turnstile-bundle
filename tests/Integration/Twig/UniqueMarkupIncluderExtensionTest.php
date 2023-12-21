<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Twig;

use Twig\Test\IntegrationTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Twig\UniqueMarkupIncluderExtension;

class UniqueMarkupIncluderExtensionTest extends IntegrationTestCase
{
    protected function getFixturesDir(): string
    {
        return __DIR__ . '/Fixtures';
    }

    protected function getExtensions(): array
    {
        return [new UniqueMarkupIncluderExtension()];
    }
}
