Configuration of the HTTP client options
========================================

The current implementation allows you to set a timeout limit for the back-end token validation.

The `timeout` and `max_duration` parameters can be configured inside the bundle configuration file:

```yaml
zmkr_cloudflare_turnstile:
    # ...
    http_client:
        options:
            timeout: <idle_timeout_in_seconds>
            max_duration: <max_execution_time_in_seconds>
```
