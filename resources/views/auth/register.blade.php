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
                    <h5 class="pt-3" style="color: #ffd900">
                        REGISTRO DE NUEVO USUARIO
                    </h5>
                    <span class="d-block">
                        Regístrate rápidamente con estos sencillos pasos
                    </span>
                </div>

                <div class="row justify-content-center">
                    <div class="col-10 col-lg-8 py-3 my-3">

                        <form method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                           @csrf

                            <div class="form-group">
                                <label for="name" style="color: #ffd900;">Nombre de usuario</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon0">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <input type="name" class="form-control" name="name" id="name" value="{{ old('name') }}" placeholder="Nombre de usuario" aria-label="name" aria-describedby="basic-addon0" required autofocus>
                                </div>
                                @if ($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong><small>{{ $errors->first('name') }}</small></strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="email" style="color: #ffd900;">Correo electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">@</span>
                                    </div>
                                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" placeholder="Correo electrónico" aria-label="email" aria-describedby="basic-addon1" required>
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
                                    <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña" aria-label="password" aria-describedby="basic-addon2" required autofocus>
                                </div>

                                @if ($errors->has('password'))
                                    <span class="text-danger" role="alert">
                                        <strong><small>{{ $errors->first('password') }}</small></strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="password-confirm" style="color: #ffd900;">Confirmar contraseña</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Repite la contraseña" aria-label="password" aria-describedby="basic-addon3" required>
                                </div>
                            </div>

                            <div class="form-group mt-5">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        Enviar datos de registro
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div> {{-- col --}}
        </div> {{-- row --}}
    </div> {{-- container --}}
@endsection