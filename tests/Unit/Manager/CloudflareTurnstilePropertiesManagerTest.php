<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;

/**
 * Cloudflare Turnstile properties manager test class
 */
class CloudflareTurnstilePropertiesManagerTest extends TestCase
{
    public function testInitialization(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', true);

        $this->assertTrue($propertiesManager->isEnabled());
        $this->assertFalse($propertiesManager->isCompatibilityModeEnabled());
        $this->assertFalse($propertiesManager->isExplicitModeEnabled());
    }

    public function testEnabledProperty(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', false);
        $this->assertFalse($propertiesManager->isEnabled());

        $propertiesManager->setEnabled(true);
        $this->assertTrue($propertiesManager->isEnabled());
    }

    public function testCompatibilityModeProperty(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', true);
        $propertiesManager->setCompatibilityMode('recaptcha');

        $this->assertTrue($propertiesManager->isCompatibilityModeEnabled());
    }

    public function testCompatibilityModePropertyWhenDisabled(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', true);
        $propertiesManager->setCompatibilityMode(null);

        $this->assertFalse($propertiesManager->isCompatibilityModeEnabled());
    }

    public function testExplicitJsLoaderProperty(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', true);
        $propertiesManager->setExplicitJsLoader('cloudflareTurnstileLoader');

        $this->assertTrue($propertiesManager->isExplicitModeEnabled());
    }

    public function testExplicitJsLoaderPropertyWhenDisabled(): void
    {
        $propertiesManager = new CloudflareTurnstilePropertiesManager('', true);
        $propertiesManager->setExplicitJsLoader(null);

        $this->assertFalse($propertiesManager->isExplicitModeEnabled());
    }
}
