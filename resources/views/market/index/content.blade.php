<div class="transfer">

	<div class="header">
		<div class="container">
			<h2>
				Resumen del mercado, todos los movimientos
			</h2>
		</div>  <!-- container -->
	</div>  <!-- header -->

{{-- <div class="scrolling-wrapper"> --}}
{{-- <ul class="nav nav-pills justify-content-center my-3 mx-0" style="font-size: .8em">
  <li class="nav-item">
    <a class="nav-link active" href="#">Todos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#">Claúsulas</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#">Libres</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled" href="#">Despidos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled" href="#">Cesiones</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled" href="#">Negociaciones</a>
  </li>
</ul> --}}
{{-- </div> --}}

	<div class="container">
		<section class="content">
			{{-- search --}}
			<div class="row">
			    <div class="col-12 col-lg-6 offset-lg-3">
			    	<form class="frmFilter" role="search" method="get" action="{{ route('market', $season_slug) }}">
				    	<div class="searchbox">
					        <label class="search-icon" for="search-by"><i class="fas fa-search"></i></label>
					        <input class="search-input form-control mousetrap filterName" name="filterName" type="text" placeholder="Buscar..." value="{{ $filterName ? $filterName : '' }}" autocomplete="off" onkeypress="submitFilterForm()">
					        <span class="search-clear"><i class="fas fa-times"></i></span>
				        </div>
			    	</form>
			    </div>
			</div>
			@if ($players->count() > 0)
				<div class="row py-2">
					@foreach ($players as $player)
						<div class="col-12 col-md-6">
							@include('market.index.card_data')
						</div>
					@endforeach
				</div> {{-- row --}}

			    <div class="regs-info clearfix py-3 px-1">
			        <div class="float-right">
			        	{!! $players->appends(Request::all())->render() !!}
						<div class="regs-info2 text-right pt-1 text-muted"><small>Registros: {{ $players->firstItem() }}-{{ $players->lastItem() }} de {{ number_format($players->total(), 0, ',', '.') }}</small></div>
			        </div>
			    </div>
			@else
				<div class="row">
					<div class="col-12 col-lg-6 offset-lg-3 p-0">
						@include('market.index.card_data_empty')
					</div>
				</div>
			@endif
		</section> {{-- content --}}
	</div> {{-- container --}}
</div> {{-- sale --}}