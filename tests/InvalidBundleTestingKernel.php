<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zemasterkrom\CloudflareTurnstileBundle\ZmkrCloudflareTurnstileBundle;

/**
 * Invalid bundle kernel that should not be allowed to start
 */
class InvalidBundleTestingKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new ZmkrCloudflareTurnstileBundle(),
            new FrameworkBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config.php');
    }
}
