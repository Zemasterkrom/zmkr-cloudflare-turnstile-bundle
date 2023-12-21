window.zmkrCloudflareTurnstileBundleCaptcha = {
    required: function (relativeElement, responseFieldName) {
        let formTarget = relativeElement;

        while (formTarget && formTarget.tagName !== 'FORM') {
            formTarget = formTarget.parentNode;
        }

        if (formTarget) {
            formTarget.addEventListener('submit', function (e) {
                let cfTurnstileResponseInput = false;

                Array.prototype.slice.call(formTarget.querySelectorAll('input') ?? []).forEach(function (element) {
                    if (element.getAttribute('type') === 'hidden' && element.getAttribute('name') === responseFieldName) {
                        cfTurnstileResponseInput = element;
                    }
                });

                if (!cfTurnstileResponseInput || !cfTurnstileResponseInput.value) {
                    e.preventDefault();
                }
            });
        } else {
            throw new Error('Unable to find Cloudflare Turnstile associated form');
        }
    }
};
