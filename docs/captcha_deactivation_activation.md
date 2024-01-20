Deactivation or activation of captchas
======================================

By default, **Cloudflare Turnstile** captchas are configured to be rendered when associated with a form. However, you can disable or enable them depending on the use case you encounter.

You can disable or enable **Cloudflare Turnstile** captchas globally in all forms by modifying the bundle configuration file:

```yaml
zmkr_cloudflare_turnstile:
    captcha:
        # ...
        enabled: false # or true
```

You can also disable or enable **Cloudflare Turnstile** captchas on a per-request basis by type-hinting the [CloudflareTurnstilePropertiesManager](../src/Manager/CloudflareTurnstilePropertiesManager.php) class in a service or a controller:

```php
<?php

namespace App\Controller;

use App\Form\Type\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;

class CloudflareTurnstileTestController extends AbstractController
{
    public function contactForm(CloudflareTurnstilePropertiesManager $propertiesManager): Response
    {
        $propertiesManager->setEnabled(false); # or true

        return $this->render('<twig_template_path>', [
            'form' => $this->createForm(ContactType::class)->createView()
        ]);
    }

    // ...
}
```
