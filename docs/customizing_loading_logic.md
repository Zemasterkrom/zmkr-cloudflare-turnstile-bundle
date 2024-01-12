Customization of the loading logic
==================================

### 1. Explicit loading

**Cloudflare Turnstile** lets you use explicit **JavaScript** loaders instead of automatically loading and displaying the **Cloudflare Turnstile** captchas in the containers with `cf-turnstile` class. An explicit **JavaScript** loader corresponds to a **JavaScript** function that will load the captchas by using a custom supplied **JavaScript** logic. The bundle will not display any captcha by default if you use explicit loading. In this case, you must supply the body of the **JavaScript** function.

To use this behavior, you need to modify the bundle configuration file:

```yaml
zmkr_cloudflare_turnstile :
    captcha :
        # ...
        explicit_js_loader: <javascript_function_name>
```

The **JavaScript** function **must** be loaded before the form is rendered.

*Example*:
```html
<script>window.onloadTurnstileCallback = function () {
    turnstile.render('#id-of-widget-container', {
        sitekey: '<sitekey>'
    }) ;
};</script>

{{ form(form) }}
```

Here, `explicit_js_loader` would correspond to `onloadTurnstileCallback`.

You can modify the explicit **JavaScript** loader on a per-request basis by type-hinting the [CloudflareTurnstilePropertiesManager](../src/Manager/CloudflareTurnstilePropertiesManager.php) class in a service or a controller:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;

class CloudflareTurnstileTestController extends AbstractController
{
    public function contactForm(CloudflareTurnstilePropertiesManager $propertiesManager)
    {
        $propertiesManager->setExplicitJsLoader('<explicit_js_loader>');

        return $this->render('<twig_template_path>', [
            'form' => $this->createForm(CloudflareTurnstileType::class)->createView()
        ]);
    }

    // ...
}
```

> [!NOTE]
> The suffix for captcha containers with the `cf-turnstile` class is **_cloudflare_turnstile_widget_container**.

### 2. Individual explicit loading

The previous explicit mode is called only once per request, since it is used as a global explicit loader. If you want to implement a custom loading logic for specific **Cloudflare Turnstile** captchas, you can use the `individual_explicit_js_loader` option inside an associated `CloudflareTurnstileType` field of your form builder:

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cf_turnstile_response', CloudflareTurnstileType::class, [
                'individual_explicit_js_loader' => '<individual_explicit_js_loader>'
            ]);
    }
}
```

The `<individual_explicit_js_loader>` **JavaScript** function will receive the HTML identifier of the captcha widget **Cloudflare Turnstile** container as an argument, so you can directly implement the loading logic for each container that will be associated with the `<individual_explicit_js_loader>` **JavaScript** function.

### 3. Compatibility mode

If you are migrating from **reCATPCHA** or **hCaptcha** to **Cloudflare Turnstile**, **Cloudflare Turnstile** provides a compatibility mode that will register the API as `grecaptcha` or `hcaptcha`. To enable the **reCATPCHA** or **hCaptcha** compatibility mode, you need to specify the mode in the :

```yaml
zmkr_cloudflare_turnstile :
    captcha :
        # ...
        compatibility_mode: <compatibility_mode> # recaptcha or hcaptcha
```

You can modify the compatibility mode on a per-request basis by type-hinting the [CloudflareTurnstilePropertiesManager](../src/Manager/CloudflareTurnstilePropertiesManager.php) class in a service or a controller:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Manager\CloudflareTurnstilePropertiesManager;

class CloudflareTurnstileTestController extends AbstractController
{
    public function contactForm(CloudflareTurnstilePropertiesManager $propertiesManager)
    {
        $propertiesManager->setCompatibilityMode('<compatibility_mode>');

        return $this->render('<twig_template_path>', [
            'form' => $this->createForm(CloudflareTurnstileType::class)->createView()
        ]);
    }

    // ...
}
```

For more information, please refer to the **Cloudflare Turnstile** [documentation](https://developers.cloudflare.com/turnstile/migration/).
