Manual captcha validation
=========================

### 1. Without [client-side error domain management](client_side_error_domain_management.md)

If you are using the Symfony framework to build an **API** and not a **monolithic** application, then you are probably not using Symfony forms. In such a case, you can manually validate the response token you just retrieved through your application and integration of **Cloudflare Turnstile** using the **Validator** component from Symfony.

Here is an example:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

class CloudflareTurnstileTestController extends AbstractController
{
    public function validateCloudflareTurnstileResponse(Request $request, ValidatorInterface $validator): JsonResponse
    {
        // Retrieve Cloudflare Turnstile captcha response token from your API payload
        $responseToken = json_decode($request->getContent(), true)['token'] ?? null;

        // Validate the response token using the Symfony Validator and implicitly the CloudflareTurnstileCaptchaValidator
        $violations = $validator->validate($responseToken, new CloudflareTurnstileCaptcha());

        if (count($violations) > 0) {
            // The siteverify API has rejected the supplied token or a network error has occurred
            return $this->json([
                'state' => 'FAILED'
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        // The siteverify API has validated the supplied token
        return $this->json([
            'state' => 'SUCCEEDED'
        ]);
    }
}
```

### 2. With [client-side error domain management](client_side_error_domain_management.md)

If you need to differentiate errors from **Cloudflare Turnstile** siteverify API and network errors, you can enable [client-side error domain management](client_side_error_domain_management.md) and catch the associated [CloudflareTurnstileApiException](../src/Exception/CloudflareTurnstileApiException.php):

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zemasterkrom\CloudflareTurnstileBundle\Exception\CloudflareTurnstileApiException;
use Zemasterkrom\CloudflareTurnstileBundle\Validator\CloudflareTurnstileCaptcha;

class CloudflareTurnstileTestController extends AbstractController
{
    public function validateCloudflareTurnstileResponse(Request $request, ValidatorInterface $validator): JsonResponse
    {
        // Retrieve Cloudflare Turnstile captcha response token from your API endpoint payload
        $responseToken = json_decode($request->getContent(), true)['token'] ?? null;

        try {
            // Validate the response token using the Symfony Validator and implicitly the CloudflareTurnstileCaptchaValidator
            $violations = $validator->validate($responseToken, new CloudflareTurnstileCaptcha());

            if (count($violations) > 0) {
                // The siteverify API has rejected the supplied token
                return $this->json([
                    'state' => 'FAILED'
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            // The siteverify API has validated the supplied token
            return $this->json([
                'state' => 'SUCCEEDED'
            ]);
        } catch (CloudflareTurnstileApiException $e) {
            // A network error occurred
            return $this->json([
                'state' => 'NETWORK_ERROR'
            ], JsonResponse::HTTP_BAD_GATEWAY);
        }
    }
}
```
