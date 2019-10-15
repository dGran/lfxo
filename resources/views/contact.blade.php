@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <style>
        body{
            background: #161b35;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container" style="margin-top: 55px">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 py-3">
                <div class="text-center px-3">
                    <img src="{{ asset('img/contact.png') }}" class="py-2">
                    <h4 class="pt-3" style="color: #00d4e4">
                        FORMULARIO DE CONTACTO
                    </h4>
                    <span class="d-block">
                        Envía una consulta / comentario / sugerencia a los administradores de LPX.
                    </span>
                    <div class="p-3 text-muted">
	                    <small>
	                    	Rellena el siguiente formulario explicando de forma detallada el motivo de tu consulta.
	                    </small>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-10 col-lg-8 py-3 my-3">

			            <section class="seccion-contacto">
		                    <form method="POST" action="{{ route('contact.sent') }}" class="contact-form {{ (Session::get('errors')) ? 'was-validated' : '' }}"  role="form" id="contact-form" class="">
		                        @csrf
		                        <div class="messages"></div> <!-- mensajes de error -->

	                            <div class="row">
	                                <div class="col-md-6">
	                                    <div class="form-group">
	                                        <label for="nombre">Nombre *</label>
	                                        <input id="nombre" type="text" name="nombre" class="form-control" placeholder="Por favor ingresa tu nombre" required="required" data-error="El nombre es requerido." value="{{ old('nombre') }}">

	                                            @if($errors->has('nombre'))
	                                                <div class="invalid-feedback">{{ $errors->first('nombre') }}</div>
	                                            @endif

	                                    </div>
	                                </div>
	                                <div class="col-md-6">
	                                    <div class="form-group">
	                                        <label for="email">E-Mail *</label>
	                                        <input id="email" type="text" name="email" class="form-control" placeholder="Por favor ingresa tu email" required="required" data-error="Email es requerido." value="{{ old('email') }}">
	                                        @if($errors->has('email'))
	                                            <div class="invalid-feedback">{{ $errors->first('email') }}</div>
	                                        @endif
	                                    </div>
	                                </div>
	                            </div>

	                            <div class="row">
	                                <div class="col-md-12">
	                                    <div class="form-group">
	                                        <label for="mensaje">Mensaje *</label>
	                                        <textarea id="mensaje" name="mensaje" class="form-control" placeholder="Tu mensaje" rows="6" required="required" data-error="Por favor incluye un mensaje."></textarea>
	                                        @if($errors->has('mensaje'))
	                                            <div class="invalid-feedback">{{ $errors->first('mensaje') }}</div>
	                                        @endif
	                                    </div>
	                                </div>
	                                <div class="col-md-12">
	                                    <input type="submit" class="btn btn-success btn-send" value="Enviar mensaje">
	                                </div>
	                            </div>

	                            <div class="row">
	                                <div class="col-md-12 mt-2">
	                                    <p class="text-muted">
	                                    	<small>
	                                        	<strong>*</strong> Campos requeridos.</p>
	                                    	</small>
	                                </div>
	                            </div>

		                    </form> <!-- fin - .contact-form -->
			            </section> <!-- fin - .seccion-contacto -->

                    </div>
                </div>

            </div> {{-- col --}}
        </div> {{-- row --}}
    </div> {{-- container --}}
@endsection

@section('breadcrumb')
	<div class="navigator">
		<div class="container">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Contacto</li>
				</ol>
			</nav>
		</div>
	</div>
@endsection