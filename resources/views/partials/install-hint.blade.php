{{-- Mobile "Add to Home Screen" hint. Shows once for phone visitors who haven't installed
     or dismissed it, and auto-detects iPhone vs Android for the right instructions.
     (No service worker on this app, so Android's auto-prompt rarely fires — we show the
     manual steps, and still use the native prompt as a bonus if it happens to appear.) --}}
<div id="pwa-install-hint" style="display:none; position:fixed; left:12px; right:12px; bottom:calc(14px + env(safe-area-inset-bottom)); z-index:9999; background:#14151a; border:1px solid #2a2a35; border-radius:16px; padding:14px 16px; box-shadow:0 14px 44px rgba(0,0,0,.55);">
    <button type="button" onclick="dismissPwaHint()" aria-label="{{ __('Close') }}" style="position:absolute; top:6px; right:9px; background:none; border:none; color:#8888a8; font-size:22px; line-height:1; cursor:pointer;">&times;</button>
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="{{ asset('icon-192.png') }}" alt="" width="44" height="44" style="border-radius:11px; flex-shrink:0;">
        <div style="flex:1; min-width:0;">
            <div style="color:#f0f0f0; font-weight:700; font-size:.95rem;">{{ __('Add BoxerOS to your phone') }} 🥊</div>
            <div id="pwa-hint-text" style="color:#9a9aa8; font-size:.8rem; margin-top:2px; line-height:1.45;"></div>
        </div>
        <button type="button" id="pwa-install-btn" style="display:none; background:linear-gradient(135deg,#96281b,#c0392b); color:#fff; border:none; border-radius:10px; padding:.55rem .95rem; font-weight:700; font-size:.85rem; cursor:pointer; white-space:nowrap;">{{ __('Install') }}</button>
    </div>
</div>
<script>
(function () {
    var KEY = 'pwa-hint-dismissed';
    var hint = document.getElementById('pwa-install-hint');
    if (!hint) return;
    var textEl = document.getElementById('pwa-hint-text');
    var btn = document.getElementById('pwa-install-btn');
    var ua = navigator.userAgent || '';
    var isIOS = /iphone|ipad|ipod/i.test(ua);
    var isAndroid = /android/i.test(ua);
    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    window.dismissPwaHint = function () {
        hint.style.display = 'none';
        try { localStorage.setItem(KEY, '1'); } catch (e) {}
    };

    if (standalone || (!isIOS && !isAndroid)) return;
    try { if (localStorage.getItem(KEY)) return; } catch (e) {}

    textEl.textContent = isIOS
        ? @json(__('Tap the Share button, then "Add to Home Screen".'))
        : @json(__('Tap the menu (⋮), then "Add to Home Screen".'));

    var deferred = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferred = e;
        textEl.textContent = @json(__('One tap to add it to your home screen.'));
        btn.style.display = 'inline-block';
    });
    btn.addEventListener('click', function () {
        if (!deferred) return;
        deferred.prompt();
        deferred.userChoice.finally(function () { dismissPwaHint(); deferred = null; });
    });

    setTimeout(function () { hint.style.display = 'block'; }, 1400);
})();
</script>
