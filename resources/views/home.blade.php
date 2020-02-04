@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <style>
        body{
            /*background: #161b35;*/
        }
    </style>
@endsection

@section('content')

    <div id="wrapper" style="background: #222B47">

        <div class="cover-page-wrap fade-in">
            <div class="cover-page-container">
                <div class="logos">
                    <div class="container">
                        <div class="row">
                            <div class="col-6 align-middle">
                                <img class="logo_game" src="img/logo_pes2020.png" alt="">
                            </div>
                            <div class="col-6 align-middle">
                                <img class="logo_platform" src="img/logo_xboxone.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

{{--         <div id="countdown_div">
            <div class="section-title">
                <div class="container">
                    <h3>
                        Comienzo de temporada
                    </h3>
                </div>
            </div>
            <div class="container text-white py-3">
                <div id="countdown" style="font-size: 2.5em; text-align: center; font-family: Helvetica,Arial,sans-serif!important; line-height: 1em;">
                    <div id="days" class="text-center" style="padding: 0 .15rem; display: inline-block;"></div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;">:</div>
                    <div id="hours" class="text-center" style="padding: 0 .15rem; min-width: 47px; display: inline-block;"></div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;">:</div>
                    <div id="minutes" class="text-center" style="padding: 0 .15rem; min-width: 47px; display: inline-block;"></div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;">:</div>
                    <div id="seconds" class="text-center" style="padding: 0 .15rem; min-width: 47px; display: inline-block; color: #ffdb03"></div>
                </div>
                <div id="countdown-text" style="font-size: 2.5em; text-align: center; line-height: .5rem; font-family: Helvetica,Arial,sans-serif!important;">
                    <div id="days" class="text-center" style="display: inline-block;">
                        <span style="font-size: .5rem">DÍAS</span>
                    </div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;"></div>
                    <div id="hours" class="text-center" style="min-width: 47px; display: inline-block;">
                        <span style="font-size: .5rem">HORAS</span>
                    </div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;"></div>
                    <div id="minutes" class="text-center" style="min-width: 47px; display: inline-block;">
                        <span style="font-size: .5rem">MINUTOS</span>
                    </div>
                    <div class="text-center" style="min-width: 12px; display: inline-block;"></div>
                    <div id="seconds" class="text-center" style="min-width: 47px; display: inline-block">
                        <span style="font-size: .5rem">SEGUNDOS</span>
                    </div>
                </div>
                <script>
                    var end = new Date('10/17/2019 0:00 AM');

                    var _second = 1000;
                    var _minute = _second * 60;
                    var _hour = _minute * 60;
                    var _day = _hour * 24;
                    var timer;

                    function showRemaining() {
                        var now = new Date();
                        var distance = end - now;
                        if (distance < 0) {

                            clearInterval(timer);
                            $("#countdown_div").addClass('d-none');
                            // document.getElementById('countdown_div').innerHTML = 'EXPIRED!';

                            return;
                        }
                        var days = Math.floor(distance / _day);
                        var hours = Math.floor((distance % _day) / _hour);
                        var minutes = Math.floor((distance % _hour) / _minute);
                        var seconds = Math.floor((distance % _minute) / _second);

                        $('.days').val(days);
                        if (hours   < 10) {hours   = "0"+hours;}
                        if (minutes < 10) {minutes = "0"+minutes;}
                        if (seconds < 10) {seconds = "0"+seconds;}
                        document.getElementById('days').innerHTML = days;
                        document.getElementById('hours').innerHTML = hours;
                        document.getElementById('minutes').innerHTML = minutes;
                        document.getElementById('seconds').innerHTML = seconds;
                    }

                    timer = setInterval(showRemaining, 1000);
                </script>
            </div>
        </div> --}}

{{--         <div class="section-title">
            <div class="container">
                <div class="clearfix p-0">
                    <h3 class="float-left">
                        Bienvenido a la Open Beta
                    </h3>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="text-white p-2">
                Puedes probar las funciones de mercado mientras ultimamos los preparativos. <a class="text-info" href="{{ route('register') }}">Regístrate</a> y pide equipo a los administradores.
                <p class="m-0 pt-2">Todos los datos de jugadores y equipos son de prueba.</p>
                <p class="m-0 pt-2">Únete al canal de Telegram (el enlace al final de esta página) para recibir las notificaciones del mercaod de fichajes. No es funcional al 100%, pronto...</p>
                <p class="m-0 pt-2">El objetivo de esta beta es la familiarización con el nuevo entorno antes de comenzar.</p>
                <p class="m-0 pt-2">Habrán pequeñas actualizaciones durante el periodo de la beta.</p>
                <p class="pt-2 m-0 text-center">🐥 🐤 🐣</p>
            </div>
        </div> --}}


        <div class="section-title">
            <div class="container">
                <div class="clearfix p-0">
                    <h3 class="float-left">
                        Últimas noticias
                    </h3>
                    @if ($posts->lastPage() > 1)
                        <div class="navigation-buttons float-right">
                            <a href="{{ $posts->previousPageUrl() }}" class="mr-2 {{ $posts->currentPage() == 1 ? 'disabled' : '' }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <span>{{ $posts->currentPage() }}</span>
                            <a href="{{ $posts->nextPageUrl() }}" class="ml-2 {{ $posts->currentPage() == $posts->lastPage() ? 'disabled' : '' }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
                <ul class="list-inline pt-2">
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == '' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home') }}">Todas</a>
                    </li>
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == 'default' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'default') }}">Informativas</a>
                    </li>
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == 'result' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'result') }}">Resultados</a>
                    </li>
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == 'transfer' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'transfer') }}">Mercado</a>
                    </li>
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == 'press' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'press') }}">Sala de prensa</a>
                    </li>
                   <li class="list-inline-item">
                        <a class="social-icon text-xs-center {{ $type == 'champion' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'champion') }}">Campeones</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    @if ($posts->count() == 0)
                        <div class="px-2 py-4 text-white">
                            No existen noticias
                        </div>
                    @else
                        <ul style="list-style: none; margin:0; padding: 0;">
                            @foreach ($posts as $post)
                                @if ($post->transfer_id || $post->match_id || $post->press_id)
                                    @if ($post->transfer_id)
                                        <a href="{{ route('market') }}">
                                    @elseif ($post->match_id && $post->match_exists())
                                        <a href="{{ route('competitions.calendar', [active_season()->id, $post->match->competition()->slug, $post->match->group()->phase_slug_if_necesary(), $post->match->group()->group_slug_if_necesary()]) }}">
                                    @elseif ($post->press_id && $post->press->participant_exists())
                                        <a href="{{ route('club.press', $post->press->participant->slug()) }}">
                                    @endif
                                @endif
                                <li class="py-2 d-block" style="display: table; border-bottom: 1px solid #292C5E">
                                    <figure style="width: 96px; height: 96px; display: table-cell; position: relative; line-height: 1.1em;" class="m-0 text-center align-top px-2">
                                        @if ($post->type == "transfer")
                                            <img src="{{ asset($post->img) }}" style="width: 100%; height: auto; background: #c3cfea; border: 1px solid #940a53" class="rounded-circle">
                                        @elseif ($post->type == "press")
                                            <img src="{{ asset($post->img) }}" style="margin: .5em; width: auto; height: 60px;" class="rounded">
                                            <small class="text-white d-inline-block text-truncate" style="max-width: 80px;">{{ $post->press->participant->user->name }}</small>
                                        @elseif ($post->type == "default")
                                            <img src="{{ asset($post->img) }}" style="width: auto; height: 80px" class="">
                                        @elseif ($post->type == "result")
                                            <img src="{{ asset($post->img) }}" style="width: auto; height: 80px" class="rounded-circle">
                                            <img src="{{ asset('img/competitions/whistle.png') }}" style="position: absolute; width: 35px; left: 58px; top: 48px" class="">
                                        @elseif ($post->type == "champion")
                                            <img src="{{ asset('img/winner.png') }}" style="width: auto; height: 80px">
                                        @endif
                                    </figure>
                                    <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                        <ul style="list-style: none; margin:0; padding: 0">
                                            <li>
                                                <span class="text-white d-block" style="font-size: .7em">
                                                    {{ $post->created_at->diffForHumans() }}
                                                </span>
                                                <span style="display: block; margin-bottom: 6px; font-size: 11px; color: #00d4e4">
                                                    {{ $post->category }}
                                                </span>
                                                <span style="display: block; font-size: 16px; font-weight: 500; line-height: 20px; color: #fff;">
                                                    {{ $post->title }}
                                                </span>
                                                <span style="display: block; font-size: 13px;line-height: 18px; color: #A4A4A4" class="mt-1">
                                                    {{ $post->description }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                @if ($post->transfer_id || $post->match_id || $post->press_id)
                                 @if ($post->transfer_id)
                                        </a>
                                    @elseif ($post->match_id && $post->match_exists())
                                        </a>
                                    @elseif ($post->press_id && $post->press->participant_exists())
                                        </a>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    @endif








{{--                     <img src="https://www.mundodeportivo.com/r/GODO/MD/p6/Barca/Imagenes/2019/03/13/Recortada/img_rguillamet_20190313-224453_imagenes_md_otras_fuentes_champions-03-klvB-U461014157231diG-980x554@MundoDeportivo-Web.jpg" class="img-fluid" alt="" width="450">
 --}}


{{--                         <li class="py-2 d-block" style="display: table; border-top: 1px solid #292C5E; width: 100%">
                            <figure style="width: 96px; display: table-cell; position: relative;" class="m-0 text-center align-top p-0">
                                <img src="https://image.flaticon.com/icons/svg/448/448003.svg" alt="" style="padding: .5em .5em 0 ,5em; width: auto; height: 60px" class="rounded">
                            </figure>
                            <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                <ul style="list-style: none; margin:0; padding: 0">
                                    <li>
                                        <span style="display: block; font-size: 16px; font-weight: 500; line-height: 20px; color: #fff;">
                                            Felicidades ferminnn!!
                                        </span>
                                        <span style="display: block; font-size: 13px;line-height: 18px; color: #A4A4A4" class="mt-1">
                                            LPX te desea un feliz día en tu 47 cumpleaños
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <span class="text-right" style="display: block; font-size: 11px; color: #fafafa">
                                 hoy, a las 3:43
                            </span>
                        </li> --}}




{{--                         <li class="py-2 d-block" style="display: table; border-top: 1px solid #292C5E">
                            <figure style="width: 96px; height: 80px; display: table-cell; position: relative;" class="m-0 text-center align-top p-0">

                                <img src="https://seeklogo.com/images/F/FC_Barcelona-logo-D941E13B46-seeklogo.com.png" alt="" style="position:absolute; left: 10px; top: 0; width: 50px; height: auto" class="rounded">
        <img src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Flamengo_FC_%28Alegrete%29_-_3.png" alt="" style="position: absolute; right: 10px; top: 15px; width: 50px; height: auto" class="rounded">



                            </figure>
                            <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                <ul style="list-style: none; margin:0; padding: 0">
                                    <li>
                                        <span style="display: block; font-size: 11px; color: #00d4e4">
                                            COPA NORTE - FINAL
                                        </span>
                                        <span style="display: block; font-size: 16px; font-weight: 500; line-height: 20px; color: #fff;">
                                            FC Barcelona vs Flamengo
                                        </span>
                                        <span style="display: block; font-size: 13px;line-height: 18px; color: #A4A4A4" class="mt-1">
                                            FC Barcelona de Maxi y Flamengo de ferminnn disputarán la final de Copa Norte
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </li> --}}


{{--                         <li class="py-2 d-block" style="display: table; border-top: 1px solid #292C5E">
                            <figure style="width: 96px; height: 80px; display: table-cell; position: relative;" class="m-0 text-center align-top p-0">

                                <img src="img/winner.png" alt="" style="padding: .5em .5em 0 ,5em; width: auto; height: 60px" class="rounded">
                                <small class="text-white" style="text-shadow: 0 0 20px #ed1e79;">pAdRoNe</small>
                            </figure>
                            <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                <ul style="list-style: none; margin:0; padding: 0">
                                    <li>
                                        <span style="display: block; font-size: 11px; color: #00d4e4">
                                            EUROPA LEAGUE - FINALIZADO
                                        </span>
                                        <span style="display: block; font-size: 16px; font-weight: 500; line-height: 20px; color: #fff;">
                                            Zenit campeón
                                        </span>
                                        <span style="display: block; font-size: 13px;line-height: 18px; color: #A4A4A4" class="mt-1">
                                            El Zenit de pAdRoNe se proclama campeón del torneo Europa League
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </li> --}}


{{--                         <li class="py-2 d-block" style="display: table; border-top: 1px solid #292C5E">
                            <figure style="width: 96px; height: 96px; display: table-cell; position: relative;" class="m-0 text-center align-top px-2">

                                <img src="img/score.png" alt="" style="padding: .5em; width: 100%; height: auto;" class="rounded">
                            </figure>
                            <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                <ul style="list-style: none; margin:0; padding: 0">
                                    <li>
                                        <span style="display: block; font-size: 11px; color: #00d4e4">
                                            EUROPA LEAGUE - GRUPO A - JORNADA 3
                                        </span>
                                        <div style="display:table-cell; vertical-align: middle; " class="text-center">
                                            <div class="d-inline-block">
                                            <span style="font-size: 12px; font-weight: 500; line-height: 20px; color: #fff;">FLAMENGO</span>
        <span style="font-size: 9px; font-weight: 400; line-height: 10px; color: #fff; display:block">ferminnn</span>
        </div>
        <div class="d-inline-block align-top ml-1">
                                            <figure class="m-0 text-center align-bottom p-0">
                                            <img src="https://ssl.gstatic.com/onebox/media/sports/logos/orE554NToSkH6nuwofe7Yg_96x96.png" alt="" style="width: 18px; height: auto;">
                                            </figure>
                                            </div>
                                        </div>
                                        <div class="px-2" style="display: table-cell; font-size: 17px; font-weight: 500; line-height: 30px; color: #fff; vertical-align: bottom; padding-bottom: 15px">
                                            2 - 0
                                        </div>
                                        <div style="display:table-cell; vertical-align: middle; " class="text-center">
        <div class="d-inline-block align-top mr-1">
                                            <figure class="m-0 text-center align-bottom p-0">
                                            <img src="https://ssl.gstatic.com/onebox/media/sports/logos/orE554NToSkH6nuwofe7Yg_96x96.png" alt="" style="width: 18px; height: auto;">
                                            </figure>
                                            </div>
                                            <div class="d-inline-block">
                                            <span style="font-size: 12px; font-weight: 500; line-height: 20px; color: #fff;">FLAMENGO</span>
        <span style="font-size: 9px; font-weight: 400; line-height: 10px; color: #fff; display:block">ferminnn</span>
        </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li> --}}

                </div>
            </div>
        </div>

        <div class="section-title">
            <div class="container">
                <h3>
                    Recién llegados
                </h3>
            </div>
        </div>
        <div class="container py-2 text-white text-center">
            <ul style="padding: 0">
                @foreach ($last_users as $user)
                    <li class="m-2" style="list-style: none; display: inline-block;">
                        <div class="text-center">
                            <img src="{{ asset($user->profile->avatar) }}" width="64">
                            <small class="d-block">{{ $user->name }}</small>
                        </div>
                    </li>

                @endforeach
            </ul>

        </div>

        <div class="section-title">
            <div class="container">
                <div class="clearfix p-0">
                    <h3 class="float-left">
                        LPX Web App
                    </h3>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="text-white px-2 py-4 ">
                Versión inicial de la App. Todavía faltan contenidos y secciones por implementar. Los diseños están preparados para versiones móviles y no de escritorio. De momento, omitir el reporte de fallos o errores encontrados tanto en diseño como en funcionalidad, gracias.
            </div>
        </div>


    </div> {{-- wrapper --}}


@endsection