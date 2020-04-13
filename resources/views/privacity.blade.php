@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
@endsection

@section('content')

	<div id="wrapper" style="background: #fff">
		<div class="container p-3" style="margin-top: 55px; border-top: none; Francois One">
			{{-- <h5 class="py-3">POLÍTICA DE PRIVACIDAD</h5> --}}
			<h3 style="font-family: 'Francois One', 'Mukta', sans-serif; padding-bottom: .5em">
				POLÍTICA DE PRIVACIDAD
			</h3>
			<p class="text-justify">
				El presente Política de Privacidad establece los términos en que LFXO usa y protege la información que es proporcionada por sus usuarios al momento de utilizar su sitio web. Esta compañía está comprometida con la seguridad de los datos de sus usuarios. Cuando le pedimos llenar los campos de información personal con la cual usted pueda ser identificado, lo hacemos asegurando que sólo se empleará de acuerdo con los términos de este documento. Sin embargo esta Política de Privacidad puede cambiar con el tiempo o ser actualizada por lo que le recomendamos y enfatizamos revisar continuamente esta página para asegurarse que está de acuerdo con dichos cambios.
			</p>
			<h5 style="font-family: 'Francois One', 'Mukta', sans-serif; border-left: 4px solid #ffd900; margin-top: 1em;padding-left: .5em">
				Información que es recogida
			</h5>
			<p class="text-justify">
				Nuestro sitio web podrá recoger información personal por ejemplo: Nombre,&nbsp; información de contacto como&nbsp; su dirección de correo electrónica e información demográfica.
			</p>
			<h5 style="font-family: 'Francois One', 'Mukta', sans-serif; border-left: 4px solid #ffd900; margin-top: 1em;padding-left: .5em">
				Uso de la información recogida
			</h5>
			<p class="text-justify">
				Nuestro sitio web emplea la información con el fin de proporcionar el mejor servicio posible, particularmente para mantener un registro de usuarios, y mejorar nuestros servicios. &nbsp;Es posible que sean enviados correos electrónicos periódicamente a través de nuestro sitio con notificaciones y otra información publicitaria que consideremos relevante para usted o que pueda brindarle algún beneficio, estos correos electrónicos serán enviados a la dirección que usted proporcione y podrán ser cancelados en cualquier momento.</p>
			<p class="text-justify">
				LFXO está altamente comprometido para cumplir con el compromiso de mantener su información segura. Usamos los sistemas más avanzados y los actualizamos constantemente para asegurarnos que no exista ningún acceso no autorizado.
			</p>
			<h5 style="font-family: 'Francois One', 'Mukta', sans-serif; border-left: 4px solid #ffd900; margin-top: 1em;padding-left: .5em">
				Cookies
			</h5>
			<p class="text-justify">
				Una cookie se refiere a un fichero que es enviado con la finalidad de solicitar permiso para almacenarse en su ordenador, al aceptar dicho fichero se crea y la cookie sirve entonces para tener información respecto al tráfico web, y también facilita las futuras visitas a una web recurrente. Otra función que tienen las cookies es que con ellas las web pueden reconocerte individualmente y por tanto brindarte el mejor servicio personalizado de su web.
			</p>
			<p class="text-justify">
				Nuestro sitio web emplea las cookies para poder identificar las páginas que son visitadas y su frecuencia. Esta información es empleada únicamente para análisis estadístico y después la información se elimina de forma permanente. Usted puede eliminar las cookies en cualquier momento desde su ordenador. Sin embargo las cookies ayudan a proporcionar un mejor servicio de los sitios web, estás no dan acceso a información de su ordenador ni de usted, a menos de que usted así lo quiera y la proporcione directamente. Usted puede aceptar o negar el uso de cookies, sin embargo la mayoría de navegadores aceptan cookies automáticamente pues sirve para tener un mejor servicio web. También usted puede cambiar la configuración de su ordenador para declinar las cookies. Si se declinan es posible que no pueda utilizar algunos de nuestros servicios.
			</p>
			<h5 style="font-family: 'Francois One', 'Mukta', sans-serif; border-left: 4px solid #ffd900; margin-top: 1em; padding-left: .5em">
				Enlaces a Terceros
			</h5>
			<p class="text-justify">
				Este sitio web pudiera contener enlaces a otros sitios que pudieran ser de su interés. Una vez que usted de clic en estos enlaces y abandone nuestra página, ya no tenemos control sobre al sitio al que es redirigido y por lo tanto no somos responsables de los términos o privacidad ni de la protección de sus datos en esos otros sitios terceros. Dichos sitios están sujetos a sus propias políticas de privacidad por lo cual es recomendable que los consulte para confirmar que usted está de acuerdo con estas.
			</p>
			<h5 style="font-family: 'Francois One', 'Mukta', sans-serif; border-left: 4px solid #ffd900; margin-top: 1em;padding-left: .5em">
				Control de su información personal
			</h5>
			<p class="text-justify">
				En cualquier momento usted puede restringir la recopilación o el uso de la información personal que es proporcionada a nuestro sitio web.&nbsp; Puede marcar o desmarcar la opción de recibir información por correo electrónico desde la configuración de su cuenta en cualquier momento.
			</p>
			<p class="text-justify">
				Esta compañía no venderá, cederá ni distribuirá la información personal que es recopilada sin su consentimiento, salvo que sea requerido por un juez con un orden judicial.
			</p>
			<p class="text-justify">
				LFXO Se reserva el derecho de cambiar los términos de la presente Política de Privacidad en cualquier momento.</p>
			<div class="text-muted pt-5 text-right">
				<small>Fecha última actualización: 12 de abril de 2020.</small>
			</div>
		</div>
	</div>

@endsection

@section('breadcrumb')
	<div class="navigator">
		<div class="container">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Política de privacidad</li>
				</ol>
			</nav>
		</div>
	</div>
@endsection

