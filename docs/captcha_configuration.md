Captcha configuration
=====================

You can take full advantage of the **Cloudflare Turnstile** configuration by using the `data-` attributes described in the **Cloudflare Turnstile** [documentation](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configurations):

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
                'attr' => [
                    'data-<name_of_the_option>' => '<option_value>'
                ]
            ]);
    }
}
```

The `data-theme` option defaults to `light` instead of `auto`. The reason behind this choice is simply that many websites are not configured to use multiple color schemes, which would create an inconsistency if the captcha is set to `dark` mode while the website uses a `blank` mode. However, you can still configure this option to set the theme you wish to apply to a specific captcha.
