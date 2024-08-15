Marking captchas as required
============================

You can prevent a form from being submitted if the captcha and associated response token are not loaded. Mark the **Cloudflare Turnstile** captcha as `required`, just as you would in standard fields such as **\<input\>** fields:

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
                'required' => true
            ]);
    }
}
```

The `required` state would then be managed automatically by the bundle, using JavaScript.
