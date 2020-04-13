<div class="sale">

	<div class="header">
		<div class="container">
			<h2>
				Jugadores transferibles, cedibles y en venta directa
			</h2>
			<div class="filters text-center p-2">
				<a data-toggle="modal" data-target="#filtersModal" id="btnFilters">
					<i class="fas fa-filter mr-1"></i>
					Filtros
				</a>
			</div>
		</div> {{-- container --}}
	</div> {{-- header --}}

	<div class="container">
		<section class="content">
			@if ($players->count() > 0)
				<div class="row py-2">
					@foreach ($players as $player)
						<div class="col-12 col-md-6 col-xl-4 py-1 px-3">
							@include('market.sale.card_data')
						</div>
					@endforeach
				</div> {{-- row --}}
			@else
				<div class="row">
					<div class="col-12 col-lg-6 offset-lg-3 p-0">
						@include('market.sale.card_data_empty')
					</div>
				</div>
			@endif
		</section> {{-- content --}}
	</div>
</div> {{-- sale --}}

<script>
	jQuery(function($) {
		$('#season_selector').on('change', function() {
			var url = $(this).val();
			if (url) {
				window.location = url;
			}
			return false;
		});
	});
</script>