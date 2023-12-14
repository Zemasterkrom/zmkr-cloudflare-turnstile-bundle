<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;

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

    protected static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
