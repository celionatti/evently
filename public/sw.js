const CACHE_NAME = "eventlyy-v1";
const urlsToCache = [
  "/",
  "/dist/css/bootstrap.min.css",
  "/dist/css/style.css",
  "/dist/js/bootstrap.bundle.min.js",
  "/dist/js/trees.js",
  "/dist/css/bootstrap-icons.css",
];

self.addEventListener("install", function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener("fetch", function (event) {
  event.respondWith(
    caches.match(event.request).then(function (response) {
      if (response) {
        return response;
      }
      return fetch(event.request);
    })
  );
});
