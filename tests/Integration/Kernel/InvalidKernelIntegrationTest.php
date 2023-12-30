<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Zemasterkrom\CloudflareTurnstileBundle\Test\InvalidBundleTestingKernel;

/**
 * Kernel test class asserting that the bundle cannot start without Twig
 */
class InvalidKernelIntegrationTest extends KernelTestCase
{
    public function testBundleBoot(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        self::bootKernel();
    }

    protected static function getKernelClass(): string
    {
        return InvalidBundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
