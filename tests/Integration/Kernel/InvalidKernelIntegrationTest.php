<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Integration\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Zemasterkrom\CloudflareTurnstileBundle\Tests\Integration\InvalidBundleTestingKernel;

/**
 * Integration test class that asserts that the bundle cannot start without the Twig Bundle
 */
class InvalidKernelIntegrationTest extends KernelTestCase
{
    public function testBundleBoot(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        self::bootKernel();
    }

    public static function getKernelClass(): string
    {
        return InvalidBundleTestingKernel::class;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
