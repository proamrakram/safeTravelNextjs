<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Page Title' }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" />

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/mdb/marta-szymanska/images/favicon.png') }}">

    <!-- Google Fonts Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- CSS Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/ltr/css/mdb.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles()

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            line-height: 1.7;
        }

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

    <section class="vh-100">
        <div class="container-fluid">
            <div class="row">

                <div class="d-flex justify-content-center align-items-center col-sm-6">
                    <div class="card-body p-md-5 mx-md-4">

                        <div class="text-center">
                            <img src="{{ asset('assets/mdb/marta-szymanska/images/brandSafeTravel.png') }}" style="width: 400px;"
                                alt="logo">
                            <h4 class="mb-5 fw-bold">{{ $headerTitle }}</h4>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $slot }}
                        </div>

                    </div>
                </div>

                <div class="col-sm-6 px-0 d-none d-sm-block">
                    <img src="{{ asset('assets/mdb/marta-szymanska/images/brand2.png') }}" alt="Login image"
                        class="w-100 vh-100" style="object-fit: cover;">
                </div>
            </div>
        </div>
    </section>


    {{-- JS Scripts --}}
    <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/ltr/js/mdb.min.js') }}"></script>
    @livewireScripts()
</body>

</html>
