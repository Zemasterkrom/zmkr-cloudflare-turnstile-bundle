<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Integration\Translation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Test\BundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

/**
 * Integration test class that checks that Cloudflare Turnstile error message translation is working properly
 */
class CloudflareTurnstileTranslationTest extends KernelTestCase
{
    private TranslatorInterface $translator;
    private static array $translationFileContents;
    private static array $errorMessageTranslations;

    public function setUp(): void
    {
        /** @phpstan-ignore-next-line */
        $this->translator = self::bootKernel()->getContainer()->get('translator');

        if (!isset(self::$translationFileContents)) {
            self::$translationFileContents = [];
        }
    
        if (!isset(self::$errorMessageTranslations)) {
            self::$errorMessageTranslations = [];
        }
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

    /**
     * @dataProvider yieldIetfBcp47SymfonyLocaleAssociations
     */
    public function testEverySupportedTranslations($ietfBcp47SymfonyLocale): void {
        $cloudflareTurnstileCaptchaConstraint = new CloudflareTurnstileCaptcha();
        $filesystem = new Filesystem();


        $translationFilePath = "";
        $completePath = sprintf("%s/../../../src/Resources/translations/validators.%s.php", __DIR__, $ietfBcp47SymfonyLocale);
        $partialPath = sprintf( "%s/../../../src/Resources/translations/validators.%s.php", __DIR__, substr($ietfBcp47SymfonyLocale, 0, 2));

        if (glob($completePath)) {
            $translationFilePath = glob($completePath)[0];
        } else if (glob($partialPath)) {
            $translationFilePath = glob($partialPath)[0];
        }

        $this->assertNotEmpty($translationFilePath);
        $this->assertTrue($filesystem->exists($translationFilePath));

        $translationFileContent = file_get_contents($translationFilePath);
        $errorMessageTranslation = $this->translator->trans($cloudflareTurnstileCaptchaConstraint->message, [], 'validators', $ietfBcp47SymfonyLocale);
        
        $this->assertNotEquals($cloudflareTurnstileCaptchaConstraint->message, $errorMessageTranslation);
        $this->assertNotContains($translationFileContent, self::$translationFileContents);
        $this->assertNotContains($errorMessageTranslation, self::$errorMessageTranslations);

        self::$translationFileContents[] = $translationFileContent;
        self::$errorMessageTranslations[] = $errorMessageTranslation;
    }

    public function yieldIetfBcp47SymfonyLocaleAssociations(): iterable {
        $ietfBcp47SymfonyLocaleAssociations = array_map(function(string $ietfBcp47locale) {
            return preg_replace_callback("/-(.*)/", function(array $matches) {
                return sprintf("_%s", strtoupper($matches[1]));
            }, $ietfBcp47locale);
        },  array_filter(array_keys(CloudflareTurnstileType::SUPPORTED_LANGUAGES_LOCALES), function(string $locale) {
            return strlen($locale) === 5;
        }));

        foreach ($ietfBcp47SymfonyLocaleAssociations as $ietfBcp47SymfonyLocaleAssociation) {
            yield [$ietfBcp47SymfonyLocaleAssociation];
        }
    }

    protected static function getKernelClass(): string
    {
        return BundleTestingKernel::class;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
