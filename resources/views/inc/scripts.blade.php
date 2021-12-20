{{-- Csrf token --}}
<meta name="csrf-token" content="{{ csrf_token() }}" />

{{-- Main js and css --}}
<script type="text/javascript" src="{{ mix('/js/manifest.js') }}" defer></script>
{{-- <script type="text/javascript" src="{{ mix('/js/vendor.js') }}" defer></script> --}}
<script type="text/javascript" src="{{ mix('/js/app.js') }}" defer></script>
<link rel="stylesheet" type="text/css" href="{{ mix('/css/style.css') }}">
