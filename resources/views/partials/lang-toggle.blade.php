@php $ul = app()->getLocale(); @endphp
{{-- Self-contained EN/FR switch - no CSS-var dependency, so it works on the app shell,
     the auth card and the public landing page alike. --}}
<form method="POST" action="{{ route('locale.set') }}"
      style="display:inline-flex; align-items:center; border:1px solid rgba(255,255,255,0.15); border-radius:8px; overflow:hidden; flex-shrink:0;">
    @csrf
    <button type="submit" name="locale" value="en"
            style="font-size:0.72rem; padding:0.25rem 0.6rem; line-height:1; border:none; cursor:pointer; {{ $ul === 'en' ? 'background:rgba(192,57,43,0.3); color:#fff; font-weight:700;' : 'background:transparent; color:rgba(255,255,255,0.5);' }}">EN</button>
    <button type="submit" name="locale" value="fr"
            style="font-size:0.72rem; padding:0.25rem 0.6rem; line-height:1; border:none; cursor:pointer; {{ $ul === 'fr' ? 'background:rgba(192,57,43,0.3); color:#fff; font-weight:700;' : 'background:transparent; color:rgba(255,255,255,0.5);' }}">FR</button>
</form>
