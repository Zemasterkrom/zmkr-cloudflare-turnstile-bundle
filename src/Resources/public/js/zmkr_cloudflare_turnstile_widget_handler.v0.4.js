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
    },
    onload: function (widgetContainerId, callbackFunctionName, msCheckInterval = 500, maxNumberOfTries = 20) {
        return new Promise((resolve, reject) => {
            const resolver = (runNumber = 1) => {
                if (window.turnstile) {
                    if (window[callbackFunctionName] && typeof window[callbackFunctionName] === "function") {
                        window[callbackFunctionName](widgetContainerId);

                        resolve({
                            widgetContainerId: widgetContainerId,
                            callbackFunctionName: callbackFunctionName,
                            runNumber: runNumber
                        });
                        return;
                    } else if (runNumber >= maxNumberOfTries) {
                        reject(`Unable to call ${callbackFunctionName} function for Cloudflare Turnstile widget container #${widgetContainerId}`);
                    }
                }

                if (runNumber >= maxNumberOfTries) {
                    reject(`Failed resolving Cloudflare Turnstile script within ${(maxNumberOfTries - 1) * msCheckInterval} milliseconds`);
                } else {
                    setTimeout(() => resolver(runNumber + 1), msCheckInterval);
                }
            };

            resolver();
        });
    }
};
