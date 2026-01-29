<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />

    <title>{{ __($title) ?? 'Page Title' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/mdb/marta-szymanska/images/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Google Font Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- MDB CSS -->
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/rtl/css/mdb.rtl.min.css') }}">
    @endif

    @if (app()->getLocale() === 'en')
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/ltr/css/mdb.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/ltr/css/new-prism.css') }}" />
    @endif

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles()


    @if (app()->getLocale() === 'ar')
        <style>
            @media (max-width: 1440px) {
                .mdb-docs-layout {
                    padding-right: 0px;
                }
            }


            .mdb-docs-layout {
                padding-right: 240px;
                transition: padding 0.3s ease;
            }
        </style>
    @endif

    @if (app()->getLocale() === 'en')
        <style>
            @media (max-width: 1440px) {
                .mdb-docs-layout {
                    padding-left: 0px;
                }
            }

            .mdb-docs-layout {
                padding-left: 240px;
                transition: padding 0.3s ease;
            }
        </style>
    @endif

    <!-- Custom Font Setup -->
    <style>
        /* @media (min-width: 1400px) {

            main,
            header,
            #main-navbar {
                padding-left: 240px;
            }
        } */

        body {
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            line-height: 1.7;
            overflow-x: hidden;

        }

        /* #sidenav-6 {
            background-color: rgb(45, 44, 44);
            width: 240px;
            height: 100vh;
            position: fixed;
            transition: 0.3s linear;
            z-index: 1030;
        }

        #main-screen {
            transition: margin 0.3s ease;
            padding: 20px;
        }

        @media (min-width: 660px) {
            #main-screen {
                margin-left: {{ app()->getLocale() === 'ar' ? '0' : '240px' }};
                margin-right: {{ app()->getLocale() === 'ar' ? '240px' : '0' }};
            }
        }

        @media (max-width: 659px) {
            #main-screen {
                margin-left: 0;
                margin-right: 0;
            }
        } */

        h1 {
            font-size: 32px;
            font-weight: 700;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
        }

        h3 {
            font-size: 24px;
            font-weight: 600;
        }

        h4 {
            font-size: 20px;
            font-weight: 600;
        }

        h5 {
            font-size: 18px;
            font-weight: 500;
        }

        h6 {
            font-size: 16px;
            font-weight: 500;
        }

        table,
        th,
        td {
            font-size: 16px;
        }

        button,
        input,
        select,
        textarea {
            font-size: 16px;
        }
    </style>
</head>

<body>
    <!-- Main Wrapper -->
    <div class="d-flex flex-column" style="min-height: 100vh;">
        <!-- Navbar -->
        <header>
            @include('partials.admin.sidenav')
            @include('partials.admin.navbar')
        </header>

        <!-- Page content -->
        <main id="main-screen" class="container-fluid m-auto flex-grow-1 mdb-docs-layout">
            {{ $slot }}
        </main>

        <!-- Footer -->
        {{-- @include('partials.admin.footer') --}}
    </div>


    <!-- MDB JS -->
    @if (app()->getLocale() === 'ar')
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/rtl/js/mdb.min.js') }}"></script>
    @endif

    @if (app()->getLocale() === 'en')
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/ltr/js/new-prism.js') }}"></script>
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/ltr/js/mdb.min.js') }}"></script>
    @endif

    <script type="text/javascript" src="{{ asset('assets/mdb/js/jquery-3.4.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/mdb/js/popper.min.js') }}"></script>

    <!-- Sidenav Responsive Script -->
    <!-- Adjust Main Content Margin -->
    <script>
        $(document).ready(function() {
            const sidenav = document.getElementById('sidenav-6');
            const mainScreen = document.getElementById('main-screen');
            const sidenavInstance = mdb.Sidenav.getInstance(sidenav);

            const adjustSidenav = () => {
                if (window.innerWidth < 660) {
                    sidenavInstance.changeMode('over');
                    sidenavInstance.hide();
                } else {
                    sidenavInstance.changeMode('side');
                    sidenavInstance.show();
                }
            };


            adjustSidenav();
            window.addEventListener('resize', adjustSidenav);
        });
    </script>

    @stack('scripts')
    @livewireScripts()
</body>

</html>
