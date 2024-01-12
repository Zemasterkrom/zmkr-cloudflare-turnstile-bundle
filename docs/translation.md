Modification of captcha rejection message translations
======================================================

The bundle already provides translations for all locales listed in the [CloudflareTurnstileType](../src/Form/Type/CloudflareTurnstileType.php) class.

You can override provided validation error translations. To do this, you need to add a translation file such as `translations/validators.<locale_code>.yaml` or `translations/validators.<locale_code>.php`. An example is `translations/validators.pt.yaml` or `translations/validators.pt_BR.yaml`.

**YAML**

```yaml
zmkr_cloudflare_turnstile:
    rejected_captcha: "<validation_message>"
```

**PHP**

```php
<?php

return [
    'zmkr_cloudflare_turnstile' => [
        'rejected_captcha' => "<validation_message>"
    ]
];
```

The `zmkr_cloudflare_turnstile.rejected_captcha` message path may be overriden by using a custom validation constraint when associating a **Cloudflare Turnstile** captcha to a [**CloudflareTurnstileType**](../src/Form/Type/CloudflareTurnstileType.php) field.


```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Form\Type\CloudflareTurnstileType;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cf_turnstile_response', CloudflareTurnstileType::class, [
                'constraints' => [
                    new CloudflareTurnstileCaptcha('<changed_message_path>')
                ]
            ]);
    }
}
```
