// Service worker: makes Cyclear installable, shows offline.html when a page can't be loaded, keeps versioned
// assets in a cache and shows push notifications. It lives at the web root so its scope covers the whole site.
'use strict';

// Bump to drop everything cached by an earlier version.
var CACHE = 'cyclear-2';
var OFFLINE_URL = '/offline.html';
var PRECACHE = [OFFLINE_URL, '/icons/logo.svg', '/icons/app-192.png'];

self.addEventListener('install', function (event) {
    event.waitUntil(caches.open(CACHE).then(function (cache) {
        return cache.addAll(PRECACHE);
    }).then(function () {
        return self.skipWaiting();
    }));
});

self.addEventListener('activate', function (event) {
    event.waitUntil(caches.keys().then(function (keys) {
        return Promise.all(keys.filter(function (key) {
            return key !== CACHE;
        }).map(function (key) {
            return caches.delete(key);
        }));
    }).then(function () {
        return self.clients.claim();
    }));
});

self.addEventListener('fetch', function (event) {
    var request = event.request;
    if (request.method !== 'GET') {
        return;
    }
    var url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }
    if (request.mode === 'navigate') {
        // Pages always come from the network, so standings and transfers are never stale.
        event.respondWith(fetch(request).catch(function () {
            return caches.match(OFFLINE_URL);
        }));
        return;
    }
    // asset() adds ?v=<content hash>, so a versioned URL never changes and can be served from the cache.
    if (url.searchParams.has('v')) {
        event.respondWith(cacheFirst(request, url));
    }
});

function cacheFirst(request, url) {
    return caches.open(CACHE).then(function (cache) {
        return cache.match(request).then(function (cached) {
            if (cached) {
                return cached;
            }
            return fetch(request).then(function (response) {
                if (response.ok) {
                    cache.put(request, response.clone());
                    cache.keys().then(function (keys) {
                        keys.forEach(function (key) {
                            var keyUrl = new URL(key.url);
                            if (keyUrl.pathname === url.pathname && keyUrl.search !== url.search) {
                                cache.delete(key);
                            }
                        });
                    });
                }
                return response;
            });
        });
    });
}

// Payload sent by App\Push\PushSender: {title, body, url}.
self.addEventListener('push', function (event) {
    var data = event.data ? event.data.json() : {};
    event.waitUntil(self.registration.showNotification(data.title || 'Cyclear', {
        body: data.body || '',
        icon: '/icons/app-192.png',
        badge: '/icons/badge-96.png',
        data: {url: data.url || '/'},
    }));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = new URL(event.notification.data.url, self.location.origin).href;
    event.waitUntil(self.clients.matchAll({type: 'window', includeUncontrolled: true}).then(function (windows) {
        for (var i = 0; i < windows.length; i++) {
            if (windows[i].url === url && 'focus' in windows[i]) {
                return windows[i].focus();
            }
        }
        return self.clients.openWindow(url);
    }));
});
