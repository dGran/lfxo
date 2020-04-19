<div class="navigator">
	<div class="container">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
				<li class="breadcrumb-item"><a href="{{ route('competitions') }}">Competiciones</a></li>
				{{-- <li class="breadcrumb-item"><a href="{{ route('competitions.competition', $group->phase->competition->slug) }}">{{ $group->phase->competition->name }}</a></li> --}}
				<li class="breadcrumb-item active" aria-current="page">{{ $group->phase->competition->name }} - Estadísticas</li>
			</ol>
		</nav>
	</div>
</div>