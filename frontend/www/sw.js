const CACHE_NAME = 'omegaup-static-v1';

const CACHEABLE_PATHS = ['/js/', '/css/', '/media/', '/third_party/'];

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_NAME)
            .map((key) => caches.delete(key)),
        ),
      ),
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (url.origin !== self.location.origin) {
    return;
  }

  if (shouldSkip(url.pathname)) {
    return;
  }

  if (isCacheable(url.pathname)) {
    if (url.pathname.startsWith('/media/')) {
      event.respondWith(staleWhileRevalidate(request));
    } else {
      event.respondWith(networkFirst(request));
    }
  }
});

function shouldSkip(pathname) {
  return (
    pathname.startsWith('/api/') ||
    pathname.startsWith('/arena/') ||
    pathname.startsWith('/login/')
  );
}

function isCacheable(pathname) {
  return CACHEABLE_PATHS.some((path) => pathname.startsWith(path));
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(CACHE_NAME);

  const cached = await cache.match(request);

  const networkFetch = fetch(request)
    .then((response) => {
      if (response.ok) {
        cache.put(request, response.clone()).catch(() => undefined);
      }

      return response;
    })
    .catch(() => cached || Response.error());

  return cached || networkFetch;
}

async function networkFirst(request) {
  const cache = await caches.open(CACHE_NAME);

  try {
    const response = await fetch(request);

    if (response.ok) {
      cache.put(request, response.clone()).catch(() => undefined);
    }

    return response;
  } catch {
    const cached = await cache.match(request);

    return cached || Response.error();
  }
}
