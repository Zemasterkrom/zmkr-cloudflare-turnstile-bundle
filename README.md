ZmkrCloudflareTurnstileBundle
=============================

[![CI Status](https://github.com/zemasterkrom/zmkr-cloudflare-turnstile-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/zemasterkrom/zmkr-cloudflare-turnstile-bundle/actions)
[![codecov](https://codecov.io/gh/zemasterkrom/zmkr-cloudflare-turnstile-bundle/graph/badge.svg)](https://codecov.io/gh/Zemasterkrom/zmkr-cloudflare-turnstile-bundle/)

**Currently under development**

Requires **Symfony `>= 5.0`** and **PHP `>= 7.4`**. Tested up to **Symfony 7**.

The purpose of this bundle is to facilitate the configuration and integration of the **Cloudflare Turnstile** captcha system into **Symfony** forms.

Summary of features provided:
- Automatic captcha rendering, language configuration and validation
- Timeout management
- Client-side error domain management
- reCAPTCHA / hCaptcha compatibility mode
- Possibility of using custom JavaScript rendering logic

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

Documentation
-------------
For advanced usage, please see the [documentation](docs/index.md).

License
-------
This bundle is licensed under the MIT license. The license is accessible [here](LICENSE).
