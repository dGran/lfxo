<div class="negotiations">
	<div class="" style="background: #EEEEEE; border-bottom: 1px solid #f1eff3; padding: .5rem .5rem 1rem .5rem;">
		<div class="container">
			<h2 class="text-center" style="color: #FC0053; font-weight: 400; font-size: 28px; line-height: 1.3em; margin: .5em 0; padding: .25rem 0;">
				Acuerdos confirmados
			</h2>
		</div> {{-- container --}}
	</div> {{-- header --}}


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