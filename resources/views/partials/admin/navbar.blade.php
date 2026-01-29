<nav class="navbar navbar-expand-lg" data-mdb-color="light" style="background-color: #2d2c2c" data-mdb-theme="dark">
    <!-- Container wrapper -->
    <div class="container">
        <!-- Toggle button -->
        <button data-mdb-collapse-init class="navbar-toggler" type="button" data-mdb-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <button data-mdb-toggle="sidenav" data-mdb-target="#sidenav-6" aria-controls="#sidenav-6"
                aria-haspopup="true" style="background: none; border: none; padding: 0; cursor: pointer;">
                <i class="fas fa-bars text-light"></i>
            </button>

            <!-- Navbar brand -->

            <a class="navbar-brand mt-2 mt-lg-0" href="#">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="logo-icon"
                    height="25" width="25" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z">
                    </path>
                </svg>
            </a>
            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-light" href="#">{{ __('Dashboard') }}</a>
                </li>
            </ul>
            <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->

        <!-- Right elements -->
        <div class="d-flex align-items-center">

            <!-- Avatar -->
            <div class="dropdown">

                <a data-mdb-dropdown-init class="dropdown-toggle d-flex align-items-center hidden-arrow mr-3"
                    href="#" id="navbarDropdownMenuAvatar" role="button" aria-expanded="false"
                    data-mdb-toggle="dropdown">

                    <img src="https://mdbcdn.b-cdn.net/img/new/avatars/2.webp" class="rounded-circle" height="25"
                        alt="Black and White Portrait of a Man" loading="lazy" />
                </a>

                <ul class="dropdown-menu" style="text-align: unset;" aria-labelledby="navbarDropdownMenuAvatar">
                    {{-- <li>
                        <a class="dropdown-item" href="#">{{ __('Profile') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">{{ __('Settings') }}</a>
                    </li> --}}
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('admin.panel.logout', ['lang' => app()->getLocale()]) }}">{{ __('Logout') }}</a>
                    </li>
                </ul>
            </div>

            <!-- Language ar + en -->
            {{-- <div class="dropdown">
                <a data-mdb-dropdown-init class="dropdown-toggle d-flex align-items-center hidden-arrow ms-3"
                    href="#" id="navbarDropdownMenuLanguage" role="button" aria-expanded="false"
                    data-mdb-toggle="dropdown">
                    <i class="fas fa-globe"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLanguage">
                    <li>
                        <a class="dropdown-item"
                            href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">{{ __('English') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}">{{ __('Arabic') }}</a>
                    </li>
                </ul>
            </div> --}}

        </div>
        <!-- Right elements -->
    </div>
    <!-- Container wrapper -->
</nav>
