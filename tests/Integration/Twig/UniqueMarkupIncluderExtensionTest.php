<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Twig;

use Symfony\Component\Form\Exception\LogicException;
use Twig\Test\IntegrationTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Twig\UniqueMarkupIncluderExtension;

class UniqueMarkupIncluderExtensionTest extends IntegrationTestCase
{
    protected function getFixturesDir(): string
    {
        return self::getFixturesDirectory();
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/Fixtures';
    }

    protected function getExtensions(): array
    {
        return [new UniqueMarkupIncluderExtension()];
    }

    public function testDifferentInclusionForSameKeyUnderStrictModeThrowsException(): void
    {
        $this->expectException(LogicException::class);

        $extension = new UniqueMarkupIncluderExtension();

        $extension->strictlyIncludeUniqueMarkup('markup', '<div id="test"></div>');
        $extension->strictlyIncludeUniqueMarkup('markup', '<div id="test2"></div>');
    }
}
