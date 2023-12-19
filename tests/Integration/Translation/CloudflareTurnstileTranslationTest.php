<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Translation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Test\ValidBundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

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
        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->assertNotEquals($cloudflareTurnstileCaptchaConstraint->message, $this->translator->trans($cloudflareTurnstileCaptchaConstraint->message, [], 'validators'));
    }

    public function testBundleTranslationWithSpecificLocale(): void
    {
        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();

        $this->assertNotEquals($cloudflareTurnstileCaptchaConstraint->message, $this->translator->trans($cloudflareTurnstileCaptchaConstraint->message, [], 'validators', 'ar'));
    }

    protected static function getKernelClass(): string
    {
        return ValidBundleTestingKernel::class;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
