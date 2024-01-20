<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test\Functional\JavaScript;

use Facebook\WebDriver\Exception\JavascriptErrorException;
use Zemasterkrom\CloudflareTurnstileBundle\Test\BundleTestingKernel;
use Zemasterkrom\CloudflareTurnstileBundle\Test\Functional\ZmkrCloudflareTurnstileBundleFunctionalTestCase;

/**
 * Functional test class checking the behavior of the Cloudflare Turnstile JavaScript widget handler (explicit JavaScript handler/loader when loading Cloudflare Turnstile)
 */
class CloudflareTurnstileTypeIndividualExplicitJsLoaderTest extends ZmkrCloudflareTurnstileBundleFunctionalTestCase
{
    public function testCaptchaIndividualExplicitModeWithoutTurnstileThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeAsyncScript("return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, null, 0, 1);");
    }

    public function testCaptchaIndividualExplicitModeWithoutExistingCallbackThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeAsyncScript("return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 0, 1);");
    }

    public function testCaptchaIndividualExplicitModeWithInvalidCallbackThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeAsyncScript(<<<EOF
            window.cloudflareTurnstileLoader = true;

            return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 0, 1);
EOF);
    }

    public function testCaptchaIndividualExplicitModeWithValidCallbackButNoTurnstileThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeAsyncScript(<<<EOF
            window.cloudflareTurnstileLoader = function() {};

            return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 0, 1);
EOF);
    }

    public function testCaptchaIndividualExplicitModeWithTurnstileButInvalidCallbackThrowsException(): void
    {
        $this->expectException(JavascriptErrorException::class);

        $this->client->executeAsyncScript(<<<EOF
            window.turnstile = {};
            window.cloudflareTurnstileLoader = true;

            return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 0, 1);
EOF);
    }

    public function testCaptchaIndividualExplicitModeWithTurnstileAndCallback(): void
    {
        $this->expectNotToPerformAssertions();

        $this->client->executeAsyncScript(<<<EOF
            window.turnstile = {};
            window.cloudflareTurnstileLoader = function() {
                return true;
            };

            return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 0, 1).then((parameters) => {
                if (!window[parameters.callbackFunctionName]()) {
                    throw parameters.callbackFunctionName;
                }

                arguments[arguments.length - 1]();
            });
EOF);
    }

    public function testCaptchaIndividualExplicitModeWaiter(): void
    {
        $this->expectNotToPerformAssertions();

        $this->client->executeAsyncScript(<<<EOF
            window.turnstile = {};
            window.cloudflareTurnstileLoader = null;

            setTimeout(() => {
                window.cloudflareTurnstileLoader = function() {
                    return true;
                };
            }, 2000);

            return window.zmkrCloudflareTurnstileBundleCaptcha.onload(null, 'cloudflareTurnstileLoader', 500, 10).then((parameters) => {
                if (parameters.runNumber < 4 || parameters.runNumber > 5) {
                    throw parameters.runNumber;
                }

                arguments[arguments.length - 1]();
            });
EOF);
    }

    protected static function getKernelClass(): string
    {
        return BundleTestingKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}
