<!-- App favicon -->
<link rel="shortcut icon" href="assets/images/favicon.ico">

<!-- Google Font Family link -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Play:wght@400;700&display=swap" rel="stylesheet">


<link href="{{asset('assets/css/vendor.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/style.min.css')}}" rel="stylesheet" type="text/css" />

{{-- Plugins: CSS --}}
@foreach(config('template.plugins', []) as $plugin)
    @continue(empty($plugin['active']))
    @foreach(($plugin['files'] ?? []) as $file)
        @if(($file['type'] ?? '') === 'css')
            @php
                $href = ($file['asset'] ?? false)
                    ? asset('assets/' . ltrim($file['location'], '/'))
                    : $file['location'];
            @endphp
            <link href="{{ $href }}" rel="stylesheet" type="text/css" />
        @endif
    @endforeach
@endforeach


