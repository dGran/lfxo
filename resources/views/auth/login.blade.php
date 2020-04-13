@extends('layouts.app_without_footer')

@section('style')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <style>
        body{
            background: #252B31;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container" style="margin-top: 55px">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 py-3">
                <div class="text-center px-3">
                    <img src="{{ asset('img/login.png') }}" class="py-2">
                    <h4 class="pt-3" style="color: #ffd900">
                        MI CUENTA
                    </h4>
                    <span class="d-block">
                        Inicia sesión para acceder a todas las opciones
                    </span>
                </div>

                @if (session('warning'))
                    <div class="pt-3 text-center text-danger">
                        Tu cuenta no está activada. Verifica tu correo electrónico y sigue las instrucciones
                        <br>
                        <a class="text-warning" href="{{ route('resendActivation') }}">Reenviar mail de verificación</a>
                    </div>
                @endif

                <div class="row justify-content-center">
                    <div class="col-10 col-lg-8 py-3 my-3">

                        <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}" class="">
                            @csrf

                            <div class="form-group">
                                <label for="email" style="color: #ffd900;">Correo electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">@</span>
                                    </div>
                                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" placeholder="Correo electrónico" aria-label="email" aria-describedby="basic-addon1" required autofocus>
                                </div>
                                @if ($errors->has('email'))
                                    <span class="text-danger" role="alert">
                                        <strong><small>{{ $errors->first('email') }}</small></strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="password" style="color: #ffd900;">Contraseña</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <input type="password" id="password" class="form-control" name="password" placeholder="Contraseña" aria-label="password" aria-describedby="basic-addon2" required>
                                </div>
                                @if ($errors->has('password'))
                                    <span class="text-danger" role="alert">
                                        <strong><small>{{ $errors->first('password') }}</small></strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        Iniciar sesión
                                    </button>
                                </div>
                            </div>

                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('password.request') }}" class="text-warning" style="font-size: .8em; display: block">
                                Olvidé mi contraseña
                            </a>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('register') }}" class="btn" style="background: #ffd900; color: #222; padding: 1em 1.5em">
                                <small class="mr-2">¿NO TIENES CUENTA?</small>REGÍSTRATE!
                            </a>
                        </div>
                    </div>
                </div>

            </div> {{-- col --}}
        </div> {{-- row --}}
    </div> {{-- container --}}
@endsection
