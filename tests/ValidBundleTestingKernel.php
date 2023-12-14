<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zemasterkrom\CloudflareTurnstileBundle\ZmkrCloudflareTurnstileBundle;

/**
 * Bundle kernel that correctly integrates required bundles.
 */
class ValidBundleTestingKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new ZmkrCloudflareTurnstileBundle(),
            new FrameworkBundle(),
            new TwigBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config.php');
    }
}
