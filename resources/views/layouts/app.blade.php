<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,minimum-scale=1.0,initial-scale=1.0,maximum-scale=1.0,user-scalable=no,viewport-fit=cover">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">

        <!-- Styles -->
        {{-- font-awesome --}}
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
        {{-- Bootstrap Select --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
        {{-- Pretty checkbox --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pretty-checkbox/3.0.0/pretty-checkbox.min.css">
        {{-- Animate --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
        {{-- Sweet Alert --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
        {{-- Progressively --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/progressively/1.2.5/progressively.css">
        {{-- ION RangeSlider --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/css/ion.rangeSlider.min.css"/>
        {{-- Summernote --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.css">
        {{-- cookies --}}
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css" />


        {{-- My styles --}}
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
        @yield('style')

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>
        {{-- Bootstrap Select --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
        {{-- Sweet Alert --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
        {{-- Mouse Trap --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mousetrap/1.6.2/mousetrap.min.js"></script>
        {{-- ION RangeSlider --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/js/ion.rangeSlider.min.js"></script>
        {{-- Progressively JS --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/progressively/1.2.5/progressively.js"></script>
        {{-- Summernote --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.js"></script>

    </head>

    <body class="d-flex flex-column">

        <div class="flex-grow-1 flex-shrink-0">
            <header>
                @include('layouts.partials.top_menu')
                @yield('section')
            </header>

            <main>
                <div id="app">
                    @include('layouts.partials.flash_message')
                    @yield('content')
                </div>
                @yield('modal')
            </main>
        </div>

        <footer class="footer flex-shrink-0">
            <div id="page-loader"><span class="preloader-interior"></span></div>
            @yield('breadcrumb')
            @include('layouts.partials.footer')
            @yield('bottom-fixed')
        </footer>


        @yield('js')
        <!-- Scripts -->
        <script src="{{ asset('js/main.js') }}"></script>

        <script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
        <script>
            window.cookieconsent.initialise({
              "palette": {
                "popup": {
                  "background": "#3c404d",
                  "text": "#d6d6d6"
                },
                "button": {
                  "background": "#8bed4f"
                }
              },
              "theme": "edgeless",
              "position": "bottom-right",
              "content": {
                "message": "Este sitio web utiliza cookies para garantizar que obtenga la mejor experiencia en nuestro sitio web.",
                "dismiss": "Aceptar",
                "link": "Leer más",
                "href": "politica-privacidad"
              }
            });
        </script>

    </body>
</html>
