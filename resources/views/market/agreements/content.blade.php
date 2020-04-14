<div class="negotiations">

	<div class="header">
		<div class="container">
			<h2>
				Acuerdos confirmados
			</h2>
		</div>
	</div>

	<div class="container">
		<div class="row justify-content-md-center">
			<div class="col-12 col-md-8 p-0">
				<section class="content mt-md-3">
					@if ($agreements->count() > 0)
						@foreach ($agreements as $trade)
							@include('market.agreements.card_data')
						@endforeach
					@else
						@include('market.agreements.card_data_empty')
					@endif
				</section> {{-- content --}}
			</div> {{-- col --}}


		</div> {{-- row --}}
	</div> {{-- container --}}

</div> {{-- negotiations --}}