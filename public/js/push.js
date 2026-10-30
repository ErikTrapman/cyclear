// Registers the service worker (public/sw.js), which makes Cyclear installable, and turns push notifications about
// transfers on and off with the [data-push-toggle] item in the menu.
(function () {
    'use strict';

    if (!('serviceWorker' in navigator)) {
        return;
    }
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js');
    });

    var configElement = document.getElementById('push-config');
    var item = document.querySelector('[data-push-toggle-item]');
    if (!configElement || !item) {
        return;
    }
    var config = JSON.parse(configElement.textContent);
    var link = item.querySelector('[data-push-toggle]');
    var label = item.querySelector('[data-push-label]');
    // The endpoint and user last sent to the server, so a page load only posts when the subscription changed
    var SYNCED_KEY = 'cyclear-push-synced';

    var supported = 'PushManager' in window && 'Notification' in window;
    // iPhones and iPads only offer push to a site opened from the home screen (iOS 16.4 and later)
    var ios = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    if (!supported && !(ios && !standalone)) {
        return;
    }
    item.hidden = false;

    var on = false;
    var busy = false;

    function setOn(value) {
        on = value;
        label.textContent = on ? 'Meldingen aan' : 'Meldingen uit';
        link.title = on ? 'Je krijgt een melding bij elke transfer. Klik om ze uit te zetten.' : 'Krijg een melding bij elke transfer.';
    }

    var noticeTimer = null;

    // Floats over the page below the menu; disappears by itself unless it asks the user to do something.
    function notice(text, type, persistent) {
        var old = document.getElementById('push-notice');
        if (old) {
            old.remove();
        }
        clearTimeout(noticeTimer);
        var alert = document.createElement('div');
        alert.id = 'push-notice';
        alert.className = 'alert alert-' + (type || 'info') + ' alert-dismissible push-toast';
        alert.setAttribute('role', persistent ? 'alert' : 'status');
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'close';
        close.setAttribute('data-dismiss', 'alert');
        close.setAttribute('aria-label', 'Sluiten');
        close.innerHTML = '&times;';
        alert.append(close, text);
        document.body.appendChild(alert);
        if (!persistent) {
            noticeTimer = setTimeout(function () {
                alert.remove();
            }, 6000);
        }
    }

    function synced(subscription) {
        try {
            return localStorage.getItem(SYNCED_KEY) === config.user + ' ' + subscription.endpoint;
        } catch (e) {
            return false;
        }
    }

    function remember(subscription) {
        try {
            if (subscription) {
                localStorage.setItem(SYNCED_KEY, config.user + ' ' + subscription.endpoint);
            } else {
                localStorage.removeItem(SYNCED_KEY);
            }
        } catch (e) {
            // Private mode; the subscription is posted again on the next page load
        }
    }

    function post(url, subscription) {
        var json = subscription.toJSON();
        return fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': config.token},
            body: JSON.stringify({endpoint: json.endpoint, p256dh: json.keys.p256dh, auth: json.keys.auth}),
        }).then(function (response) {
            if (response.ok) {
                return;
            }
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                var message = (data.errors && data.errors[0]) || (data.violations && data.violations[0] && data.violations[0].title);
                throw new Error(message || 'Dat lukte niet, probeer het later nog eens.');
            });
        });
    }

    function applicationServerKey() {
        var base64 = (config.publicKey + '='.repeat((4 - config.publicKey.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(base64), function (c) {
            return c.charCodeAt(0);
        });
    }

    function subscribe() {
        return Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') {
                notice('Je browser blokkeert meldingen van Cyclear. Sta ze toe in de instellingen van je browser en probeer het opnieuw.', 'warning', true);
                return;
            }
            return navigator.serviceWorker.ready.then(function (registration) {
                return registration.pushManager.subscribe({userVisibleOnly: true, applicationServerKey: applicationServerKey()});
            }).then(function (subscription) {
                return post(config.subscribeUrl, subscription).then(function () {
                    remember(subscription);
                    setOn(true);
                    notice('Meldingen staan aan: je krijgt een melding bij elke transfer van een andere ploeg.', 'success');
                }, function (error) {
                    subscription.unsubscribe();
                    throw error;
                });
            });
        });
    }

    function unsubscribe() {
        return navigator.serviceWorker.ready.then(function (registration) {
            return registration.pushManager.getSubscription();
        }).then(function (subscription) {
            if (!subscription) {
                return;
            }
            return post(config.unsubscribeUrl, subscription).then(function () {
                return subscription.unsubscribe();
            });
        }).then(function () {
            remember(null);
            setOn(false);
            notice('Meldingen staan uit.');
        });
    }

    link.addEventListener('click', function (event) {
        event.preventDefault();
        // On phones the menu is open; close it like any other menu choice, so the notice isn't drawn over it
        var menu = item.closest('.navbar-collapse.in');
        var menuToggle = menu && document.querySelector('[data-toggle="collapse"][data-target="#' + menu.id + '"]');
        if (menuToggle) {
            menuToggle.click();
        }
        if (!supported) {
            notice('Op iPhone en iPad werken meldingen alleen als Cyclear op je beginscherm staat: tik op Deel en kies "Zet op beginscherm". Open Cyclear daarna vanaf je beginscherm en zet de meldingen aan.', 'info', true);
            return;
        }
        if (busy) {
            return;
        }
        busy = true;
        (on ? unsubscribe() : subscribe()).catch(function (error) {
            notice(error.message, 'danger', true);
        }).then(function () {
            busy = false;
        });
    });

    if (!supported) {
        return;
    }
    navigator.serviceWorker.ready.then(function (registration) {
        return registration.pushManager.getSubscription();
    }).then(function (subscription) {
        setOn(subscription !== null && Notification.permission === 'granted');
        // Tells the server about a renewed subscription, or about another user logged in on this browser
        if (on && !synced(subscription)) {
            post(config.subscribeUrl, subscription).then(function () {
                remember(subscription);
            }, function () {
            });
        }
    });
})();
