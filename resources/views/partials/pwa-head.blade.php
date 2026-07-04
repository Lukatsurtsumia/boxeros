{{-- Installable-app (PWA) tags: home-screen icon, full-screen, app feel.
     No service worker — Add to Home Screen works without one, and it kept causing
     slow/white loads. We actively unregister any old one below. --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#0b0c0e">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="BoxerOS">
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations()
            .then(function (regs) { regs.forEach(function (r) { r.unregister(); }); })
            .catch(function () {});
        if (window.caches && caches.keys) {
            caches.keys().then(function (keys) { keys.forEach(function (k) { caches.delete(k); }); }).catch(function () {});
        }
    }
</script>
