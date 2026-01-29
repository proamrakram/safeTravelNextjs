<div id="sidenav-6" class="sidenav sidenav-sm sidenav-right" data-mdb-accordion="true" data-mdb-hidden="false"
    data-mdb-mode="side" role="navigation" data-mdb-right="false" data-mdb-color="light" style="background-color: #2d2c2c">

    <a class="ripple d-flex justify-content-center py-4 mb-3" style="padding-top: 4rem !important;"
        href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}" data-mdb-ripple-color="primary">
        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="logo-icon"
            height="60" width="60" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z">
            </path>
        </svg>
    </a>

    @php
        $locale = app()->getLocale() === 'en' ? true : false;
    @endphp

    <ul class="sidenav-menu px-2 pb-5">

        <li class="sidenav-item">
            <a class="sidenav-link" href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}">
                <i class="fas fa-tachometer-alt fa-fw {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Overview') }}</span>
            </a>
        </li>

        <hr />

        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="far fa-list-alt {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Users') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link" href="{{ route('admin.panel.users', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-plus fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Users') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="far fa-list-alt {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Registrations') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.registrations', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-plus fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Registrations') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="far fa-list-alt {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Contact Messages') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.contact-messages', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-plus fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Contact Messages') }}</span>
                    </a>
                </li>
            </ul>
        </li>

    </ul>

</div>
