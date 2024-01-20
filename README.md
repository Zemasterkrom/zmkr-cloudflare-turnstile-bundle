ZmkrCloudflareTurnstileBundle
=============================

[![CI Status](https://github.com/zemasterkrom/zmkr-cloudflare-turnstile-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/zemasterkrom/zmkr-cloudflare-turnstile-bundle/actions)
[![codecov](https://codecov.io/gh/zemasterkrom/zmkr-cloudflare-turnstile-bundle/graph/badge.svg)](https://codecov.io/gh/Zemasterkrom/zmkr-cloudflare-turnstile-bundle/)

Requires **Symfony `>= 5.0`** and **PHP `>= 7.4`**. Tested up to **Symfony 7**.

The purpose of this bundle is to facilitate the configuration and integration of the **Cloudflare Turnstile** captcha system into **Symfony** forms.

Summary of features provided:
- Automatic captcha rendering, language configuration and validation
- Timeout management
- Client-side error domain management
- reCAPTCHA / hCaptcha compatibility mode
- Possibility of using custom JavaScript rendering logic

Get started
-----------

### With Symfony Flex

Run the following command with **Composer**:

`composer require zemasterkrom/zmkr-cloudflare-turnstile-bundle`

If you have not explicitly allowed "contrib" recipes in your *composer.json* file, you will be prompted with the following message for `zemasterkrom/zmkr-cloudflare-turnstile-bundle`:

> Do you want to execute this recipe?

Answer **yes** for `zemasterkrom/zmkr-cloudflare-turnstile-bundle`.

Finally, modify the `zemasterkrom/zmkr-cloudflare-turnstile-bundle` section of the `.env` file to add your **Cloudflare Turnstile** application keys:

```
###> zemasterkrom/zmkr-cloudflare-turnstile-bundle ###
CLOUDFLARE_TURNSTILE_SITEKEY=<sitekey>
CLOUDFLARE_TURNSTILE_SECRET_KEY=<secret_key>
###< zemasterkrom/zmkr-cloudflare-turnstile-bundle ###
```

### Without Symfony Flex

Install **Composer** dependencies:

`composer require zemasterkrom/zmkr-cloudflare-turnstile-bundle`

Register the bundle inside the `config/bundles.php` file:

```php
return [
    //...
    Zemasterkrom\CloudflareTurnstileBundle\ZmkrCloudflareTurnstileBundle::class => ['all' => true],
];
```

Create the `config/packages/zmkr_cloudflare_turnstile.yaml` configuration file:

```yaml
zmkr_cloudflare_turnstile:
    captcha:
        sitekey: "%env(CLOUDFLARE_TURNSTILE_SITEKEY)%"
        secret_key: "%env(CLOUDFLARE_TURNSTILE_SECRET_KEY)%"
```

Add your **Cloudflare Turnstile** application keys to the `.env` file:

```
CLOUDFLARE_TURNSTILE_SITEKEY=<sitekey>
CLOUDFLARE_TURNSTILE_SECRET_KEY=<secret_key>
```

Usage
-----

To add a **Cloudflare Turnstile** captcha to a form, you need to associate a `CloudflareTurnstileType` field in your form builder:

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'First name'
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last name'
            ])
            ->add('cf_turnstile_response', CloudflareTurnstileType::class);
    }
}
```

Validation will be performed automatically when you check the form that is associated with a ``CloudflareTurnstileType``:

```php
<?php

namespace App\Controller;

use App\Form\Type\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareTurnstileTestController extends AbstractController
{
    public function validateContactForm(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The form has been submitted and is valid: execute your actions here
        }

        // The form has not been submitted or is invalid: if there is an error, an error message will be registered in your form

        return $this->render('<twig_template_path>', [
            'form' => $form->createView(),
        ]);
    }
}
```

Documentation
-------------
For advanced usage, please see the [documentation](docs/index.md).

License
-------
This bundle is licensed under the MIT license. The license is accessible [here](LICENSE).
