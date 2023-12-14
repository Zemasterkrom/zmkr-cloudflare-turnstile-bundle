function registerRequiredHandler(widgetContainer) {
    let cfTurnstileWidgetContainer = widgetContainer ?? (document.currentScript ? document.currentScript.parentNode : {});
    let formTarget = cfTurnstileWidgetContainer;

    while (formTarget && formTarget.tagName !== 'FORM') {
        formTarget = formTarget.parentNode;
    }

    if (formTarget) {
        formTarget.addEventListener('submit', function (e) {
            let cfTurnstileResponseInput = formTarget.querySelector('input[type="hidden"][name="cf-turnstile-response"]');

            if (!cfTurnstileResponseInput || !cfTurnstileResponseInput.value) {
                e.preventDefault();
            }
        });
    } else {
        throw new Error('Unable to find Cloudflare Turnstile associated form');
    }
}

registerRequiredHandler();
