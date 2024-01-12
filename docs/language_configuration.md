Configuration of the captcha language
=====================================

By default, the language of **Cloudflare Turnstile** captchas is automatically set to match the language of the request, so you don't need to configure it manually. However, if you need to change the language of a specific captcha, you can do so by using the `data-language` attribute option inside your form builder to one of the supported languages listed in the [CloudflareTurnstileType](../src/Form/Type/CloudflareTurnstileType.php) class:

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
                    'data-language' => '<locale_code>'
                ]
            ]);
    }
}
```

To match the **Cloudflare Turnstile** supported languages formatting, the supplied locale will automatically be formatted :
- Underscores (_) will be replaced by hyphens (-)
- The locale will be converted to lowercase
