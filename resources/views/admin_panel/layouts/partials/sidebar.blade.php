<div class="app-sidebar">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="{{ route( 'admin::home') }}" class="logo-dark">
            <img src="{{asset('assets/images/logo-sm.png')}}" class="logo-sm" alt="logo sm">
            <img src="{{asset('assets/images/logo-dark.png')}}" class="logo-lg" alt="logo dark">
        </a>

        <a href="{{ route( 'admin::home') }}" class="logo-light">
            <img src="{{asset('assets/images/logo-sm.png')}}" class="logo-sm" alt="logo sm">
            <img src="{{asset('assets/images/logo-dark.png')}}" class="logo-lg" alt="logo dark">
        </a>
    </div>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            @foreach(config('template.template_menu') as $item)
                @can($item['can'])
                    @php
                        $hasSub = isset($item['submenu']) && is_array($item['submenu']) && count($item['submenu']) > 0;
                        $collapseId = \Illuminate\Support\Str::slug($item['text'], '-') . '-menu';
                    @endphp

                    <li class="nav-item">
                        <a
                            class="nav-link {{ $hasSub ? 'menu-arrow' : '' }}"
                            @if($hasSub)
                                href="#{{ $collapseId }}"
                            data-bs-toggle="collapse"
                            role="button"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}"
                            @else
                                href="{{ !empty($item['url']) ? route($item['url']) : '#' }}"
                            @endif
                        >
                        <span class="nav-icon">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                            <span class="nav-text">{{ $item['text'] }}</span>
                        </a>

                        @if($hasSub)
                            <div class="collapse" id="{{ $collapseId }}">
                                <ul class="nav sub-navbar-nav">
                                    @foreach($item['submenu'] as $subitem)
                                        <li class="sub-nav-item">
                                            <a class="sub-nav-link" href="{{ route($subitem['url']) }}">
                                                @if(!empty($subitem['icon']))
                                                    <i class="{{ $subitem['icon'] }}"></i>
                                                @endif
                                                {{ $subitem['text'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endcan
            @endforeach
        </ul>
    </div>
</div>

@include('admin_panel.layouts.partials.animated_efect')


