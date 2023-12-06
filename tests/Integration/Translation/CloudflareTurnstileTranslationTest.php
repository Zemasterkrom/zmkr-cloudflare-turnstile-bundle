<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Tests\Integration\Translation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Tests\ValidBundleTestingKernel;

/**
 * Integration test class that checks that Cloudflare Turnstile error message translation is working properly
 */
class CloudflareTurnstileTranslationTest extends KernelTestCase
{
    private TranslatorInterface $translator;

    public function setUp(): void
    {
        /** @phpstan-ignore-next-line */
        $this->translator = self::bootKernel()->getContainer()->get('translator');
    }

    public function testBundleTranslation(): void
    {
        $this->assertNotEquals('rejected_captcha', $this->translator->trans('rejected_captcha', [], 'validators'));
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
