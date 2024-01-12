Client-side error domain management
===================================

By default, all errors are converted to form errors in order to hide the details of technical errors from the end user. This means that any HTTP error will be converted to a form error. If you want or have implemented a custom exception handler, you may want to allow exceptions to propagate in the bundle configuration file:

```yaml
zmkr_cloudflare_turnstile:
    # ...
    error_manager:
        throw_on_core_failure: true
```
