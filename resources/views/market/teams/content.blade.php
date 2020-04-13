<div class="teams">

	<div class="header">
		<div class="container">
			<h2>
				Listado de equipos
			</h2>
		</div> {{-- container --}}
	</div> {{-- header --}}

	<section class="content">
		<div class="container">
			<div class="row pb-4 px-2">
			@foreach ($participants as $participant)
				<div class="col-12 col-md-6 col-lg-4 col-xl-3">
					@include('market.teams.card_data')
				</div>
			@endforeach
			</div>
		</div>
	</section> {{-- content --}}

</div> {{-- teams --}}