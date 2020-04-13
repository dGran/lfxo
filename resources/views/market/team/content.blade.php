<div class="team">

	<div class="header sticky" id="header">
		<div class="container p-0">
			<img class="logo" src="{{ $participant->logo() }}">
			<div class="participant">
				<span class="name">
					{{ $participant->name() }}
				</span>
				<span class="subname">
					<strong>Manager: </strong>{{ $participant->sub_name() }}
				</span>
			</div>
		</div>
	</div> {{-- header --}}

	<section class="content">

		<div class="info">
	    	<div class="item clearfix">
	    		<div class="container">
		    		<span class="title">
		    			Presupuesto
		    		</span>
		    		<div class="description budget">
		                <i class="icon icon-safe-box"></i>
						<span class="data {{ number_format($participant->budget() < 0) ? 'text-danger' : '' }}">
		                	{{ number_format($participant->budget(), 2, ',', '.') }} M.
						</span>
						@if (number_format($participant->budget() < 0))
							<i class="warning fas fa-exclamation-triangle animated infinite flash"></i>
						@endif
		    		</div>
	    		</div>
	    	</div>
	    	<div class="item clearfix">
	    		<div class="container">
		    		<span class="title">
		    			Total salarios
		    		</span>
		    		<div class="description">
		    			<span class="data">
		                	{{ number_format($participant->salaries(), 2, ',', '.') }} M.
	                	</span>
						@if ($participant->salaries() > active_season()->salary_cap)
							<i class="warning fas fa-exclamation-triangle"></i>
						@endif
		                <small class="info d-block">Tope salarial: {{ number_format(active_season()->salary_cap, 2, ',', '.') }} M.</small>
		    		</div>
	    		</div>
	    	</div>
	    	<div class="item clearfix">
	    		<div class="container">
		    		<span class="title">
		    			Claúsulas
		    		</span>
		    		<div class="description">
		    			<span class="data d-block">
		    				<small class="text-muted mr-2">PAGADAS</small>
		    				{{ $participant->paid_clauses }} / {{ active_season()->max_clauses_paid }}
			    		</span>
		    			<span class="data d-block">
		    				<small class="text-muted mr-2">RECIBIDAS</small>
		    				{{ $participant->clauses_received }} / {{ active_season()->max_clauses_received }}
			    		</span>
		    		</div>
	    		</div>
	    	</div>
	    	<div class="item clearfix">
	    		<div class="container">
		    		<span class="title">
		    			Plantilla
		    		</span>
		    		<div class="description">
		    			<span class="data">
		                	{{ $participant->players->count() }} jugadores
		    			</span>
						@if ($participant->players->count() > active_season()->max_players || $participant->players->count() < active_season()->min_players)
							<i class="warning fas fa-exclamation-triangle"></i>
						@endif
						<small class="info d-block">{{ active_season()->min_players }} min. / {{ active_season()->max_players }} max.</small>
		    		</div>
	    		</div>
	    	</div>

		</div> {{-- info --}}

		<div class="positions">
			<h5 class="title clearfix">
				<div class="container">
					<span class="float-left">Porteros</span>
					<img class="icon d-block d-md-none float-right" src="{{ asset('img/clubs/pt.png') }}">
				</div>
			</h5>
			<div class="detail container">
				<div class="row pb-2">
					@foreach ($players as $player)
						@if ($player->player->position == 'PO')
							@include('market.team.card_data')
						@endif
					@endforeach
				</div>
			</div>
		</div> {{-- positions --}}

		<div class="positions">
			<h5 class="title clearfix">
				<div class="container">
					<span class="float-left">Defensas</span>
					<img class="icon d-block d-md-none float-right" src="{{ asset('img/clubs/ct.png') }}">
				</div>
			</h5>
			<div class="detail container">
				<div class="row pb-2">
					@foreach ($players as $player)
						@if ($player->player->position == 'DFC' || $player->player->position == 'LD' || $player->player->position == 'LI' || $player->player->position == 'CAD' || $player->player->position == 'CAI')
							@include('market.team.card_data')
						@endif
					@endforeach
				</div>
			</div>
		</div> {{-- positions --}}

		<div class="positions">
			<h5 class="title clearfix">
				<div class="container">
					<span class="float-left">Medios</span>
					<img class="icon d-block d-md-none float-right" src="{{ asset('img/clubs/mc.png') }}">
				</div>
			</h5>
			<div class="detail container">
				<div class="row pb-2">
					@foreach ($players as $player)
						@if ($player->player->position == 'MCD' || $player->player->position == 'MC' || $player->player->position == 'MCO' || $player->player->position == 'MD' || $player->player->position == 'MI')
							@include('market.team.card_data')
						@endif
					@endforeach
				</div>
			</div>
		</div> {{-- positions --}}

		<div class="positions">
			<h5 class="title clearfix">
				<div class="container">
					<span class="float-left">Delanteros</span>
					<img class="icon d-block d-md-none float-right" src="{{ asset('img/clubs/dc.png') }}">
				</div>
			</h5>
			<div class="detail container">
				<div class="row pb-2">
					@foreach ($players as $player)
						@if ($player->player->position == 'DC' || $player->player->position == 'SD' || $player->player->position == 'EI' || $player->player->position == 'ED')
							@include('market.team.card_data')
						@endif
					@endforeach
				</div>
			</div>
		</div> {{-- positions --}}

	</section> {{-- content --}}

</div> {{-- my-team --}}