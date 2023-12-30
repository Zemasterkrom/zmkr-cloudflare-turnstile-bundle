<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Test;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Zemasterkrom\CloudflareTurnstileBundle\Client\CloudflareTurnstileClient;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstileErrorManager;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;
use Zemasterkrom\CloudflareTurnstileBundle\Twig\UniqueMarkupIncluderExtension;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptchaValidator;
use Zemasterkrom\CloudflareTurnstileBundle\ZmkrCloudflareTurnstileBundle;

/**
 * Valid bundle kernel that correctly integrates required bundles and marks services as public in order to test their integration
 */
class ValidBundleTestingKernel extends Kernel
{
    const SERVICES = [
        CloudflareTurnstilePropertiesManager::class,
        CloudflareTurnstileErrorManager::class,
        CloudflareTurnstileType::class,
        CloudflareTurnstileCaptchaValidator::class,
        CloudflareTurnstileClient::class,
        UniqueMarkupIncluderExtension::class
    ];

    const SERVICES_ALIASES = [
        CloudflareTurnstilePropertiesManager::class
    ];

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

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class implements CompilerPassInterface
        {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $definition) {
                    if (in_array($definition->getClass(), ValidBundleTestingKernel::SERVICES)) {
                        $definition->setPublic(true);
                    }
                }

                foreach ($container->getAliases() as $id => $definition) {
                    if (in_array($id, ValidBundleTestingKernel::SERVICES_ALIASES)) {
                        $definition->setPublic(true);
                    }
                }
            }
        });
    }
}
