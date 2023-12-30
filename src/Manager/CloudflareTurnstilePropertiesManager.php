<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Manager;

/**
 * Cloudflare Turnstile property container that provides manual configurators for per-request configuration
 */
class CloudflareTurnstilePropertiesManager
{
    private string $sitekey;
    private bool $enabled;
    private ?string $explicitJsLoader;
    private bool $isExplicitModeEnabled;
    private ?string $compatibilityMode;
    private bool $isCompatibilityModeEnabled;

    /**
     * Constructor of the Cloudflare Turnstile proprieties manager
     *
     * @param string $sitekey The Cloudflare Turnstile sitekey for captcha integration
     * @param bool $enabled Flag indicating whether the captcha is enabled
     * @param string|null $explicitJsLoader If explicit loading is used, the referenced function will be called to load the captcha instead of using the default loading process
     * @param string|null $compatibilityMode Compatibility flag with other captchas (@see https://developers.cloudflare.com/turnstile/migration/)
     */
    public function __construct(string $sitekey, bool $enabled, string $explicitJsLoader = null, string $compatibilityMode = null)
    {
        $this->sitekey = $sitekey;
        $this->enabled = $enabled;
        $this->setExplicitJsLoader($explicitJsLoader);
        $this->setCompatibilityMode($compatibilityMode);
    }

    public function getSitekey(): string
    {
        return $this->sitekey;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setExplicitJsLoader(?string $explicitJsLoader): void
    {
        $this->explicitJsLoader = $explicitJsLoader;
        $this->isExplicitModeEnabled = !empty($explicitJsLoader);
    }

    public function &isExplicitModeEnabled(): bool
    {
        return $this->isExplicitModeEnabled;
    }

    /**
     * @return string|null
     */
    public function &getExplicitJsLoader()
    {
        return $this->explicitJsLoader;
    }

    public function setCompatibilityMode(?string $compatibilityMode): void
    {
        $this->compatibilityMode = $compatibilityMode;
        $this->isCompatibilityModeEnabled = !empty($compatibilityMode);
    }

    public function &isCompatibilityModeEnabled(): bool
    {
        return $this->isCompatibilityModeEnabled;
    }

    /**
     * @return string|null
     */
    public function &getCompatibilityMode()
    {
        return $this->compatibilityMode;
    }
}
