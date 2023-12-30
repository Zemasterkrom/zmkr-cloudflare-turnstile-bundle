<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClientInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Twig\UniqueMarkupIncluderExtension;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;

/**
 * Kernel test class asserting that the bundle can start with valid configuration.
 */
class ValidKernelIntegrationTest extends KernelTestCase
{
    private ContainerInterface $backwardCompatibleContainer;

    public function setUp(): void
    {
        self::bootKernel();

        /** @disregard P1013 Undefined method */
        /** @disregard P1014 Undefined property */
        /** @phpstan-ignore-next-line */
        $this->backwardCompatibleContainer = method_exists($this, 'getContainer') ? static::getContainer() : self::$container;
    }

    public function testPropertiesManagerAutowiring(): void
    {
        /** @var CloudflareTurnstilePropertiesManager */
        $propertiesManager = $this->backwardCompatibleContainer->get(CloudflareTurnstilePropertiesManager::class);

        $this->assertSame('<sitekey>', $propertiesManager->getSitekey());
        $this->assertNull($propertiesManager->getExplicitJsLoader());
        $this->assertNull($propertiesManager->getCompatibilityMode());

        $this->assertTrue($this->backwardCompatibleContainer->has('zmkr_cloudflare_turnstile.services.manager.error_manager'));
        $this->assertTrue($this->backwardCompatibleContainer->has('zmkr_cloudflare_turnstile.services.type.type'));
        $this->assertTrue($this->backwardCompatibleContainer->has('zmkr_cloudflare_turnstile.services.validator.validator'));
        $this->assertTrue($this->backwardCompatibleContainer->has('zmkr_cloudflare_turnstile.services.client.client'));
        $this->assertTrue($this->backwardCompatibleContainer->has('zmkr_cloudflare_turnstile.services.twig.unique_markup_includer_extension'));

        $this->assertInstanceOf(CloudflareTurnstileErrorManager::class, $this->backwardCompatibleContainer->get('zmkr_cloudflare_turnstile.services.manager.error_manager'));
        $this->assertInstanceOf(CloudflareTurnstileType::class, $this->backwardCompatibleContainer->get('zmkr_cloudflare_turnstile.services.type.type'));
        $this->assertInstanceOf(CloudflareTurnstileCaptchaValidator::class, $this->backwardCompatibleContainer->get('zmkr_cloudflare_turnstile.services.validator.validator'));
        $this->assertInstanceOf(CloudflareTurnstileClientInterface::class, $this->backwardCompatibleContainer->get('zmkr_cloudflare_turnstile.services.client.client'));
        $this->assertInstanceOf(UniqueMarkupIncluderExtension::class, $this->backwardCompatibleContainer->get('zmkr_cloudflare_turnstile.services.twig.unique_markup_includer_extension'));
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
