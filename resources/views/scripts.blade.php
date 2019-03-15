{{-- Global configuration object --}}
@php
$config = [
  'appName' => config('app.name'),
  'locale'  => $locale = app()->getLocale()
];
@endphp
<script>
  window.config = @json($config);
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-polyfills/0.1.42/polyfill.min.js" integrity="sha256-/XfEHUGimdIk42Vy7oTnNLtT8sVrO6vnhhnsQT1W1oo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.5.3/bluebird.min.js" integrity="sha256-vlGkKVvcIflk/184mw0as3dEIHjtrk6k0IEGqMffPTk=" crossorigin="anonymous"></script>

<script src="{{ mix('js/app.js') }}"></script>
