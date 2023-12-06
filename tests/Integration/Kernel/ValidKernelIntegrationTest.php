<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Integration\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Tests\ValidBundleTestingKernel;

/**
 * Integration test class that asserts that the bundle can start with required bundles
 */
class ValidKernelIntegrationTest extends KernelTestCase
{
    public function testBundleBoot(): void
    {
        $this->expectNotToPerformAssertions();

        self::bootKernel();
    }

    public static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
