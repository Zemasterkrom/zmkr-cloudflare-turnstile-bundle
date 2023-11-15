<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Tests\ValidBundleTestingKernel;

/**
 * Integration test class that asserts that the bundle can start with required bundles
 */
class KernelIntegrationTest extends KernelTestCase
{
    public function testBundleBoot()
    {
        $this->expectNotToPerformAssertions();

        self::bootKernel();
    }

    public static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }
}
