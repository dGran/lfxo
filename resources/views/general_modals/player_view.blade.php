<div class="modal-content">

    <div class="modal-header info">
    	<h4>{{ $player->player->name }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
    	<div class="row justify-content-center p-1 p-lg-3">
			<div class="col-12 col-sm-5 mb-3 mb-md-0">
{{-- 				<div class="player-card">
				    <div class="overall">{{ $player->player->overall_rating }}</div>
			    	<div class="position">{{ $player->player->position }}</div>
			    	<div class="name">{{ $player->player->name }}</div>
			    		@if ($player->participant)
			            	<img class="team-logo" src="{{ $player->participant->logo() }}">
			            @else
			            	<img class="team-logo" src="{{ asset('img/team_no_image.png') }}">
			            @endif
			        	<img class="img" src="{{ $player->player->getImgFormatted() }}">
			    	<div class="stats left">
			        	88 <span class="stat-name">VEL</span><br>
			        	89 <span class="stat-name">REG</span><br>
			        	83 <span class="stat-name">PAS</span>
			    	</div>
			    	<div class="stats right">
			        	91 <span class="stat-name">TIR</span><br>
			        	86 <span class="stat-name">FIS</span><br>
			        	49 <span class="stat-name">DEF</span>
			    	</div>
			    	<div class="stats-bg"></div>
			        <a target="_blank" href="{{ pesdb_player_info_path($player->player->game_id) }}">
			        	<div class="clicktrap"></div>
			    	</a>
			    </div> --}}

				<div class="card">
					<img class="img" src="{{ $player->player->getImgFormatted() }}">
					<span class="position">
						{{ $player->player->position }}
					</span>
					<span class="overall">
						{{ $player->player->overall_rating }}
					</span>
					<figure class="ball">
						<img src="{{ asset($player->player->getBall()) }}">
					</figure>
				</div>
			</div>

			<div class="col-12 col-sm-7">
	            <div class="details" style="font-size: .9em">
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Equipo
	            		</span>
	            		<div class="data">
	            			@if ($player->participant)
	                        	{{ $player->participant->name() }}
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Manager
	            		</span>
	            		<div class="data">
	            			@if ($player->participant)
	                        	{{ $player->participant->sub_name() }}
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Salario
	            		</span>
	            		<div class="data">
	                        {{ number_format($player->salary, 2, ',', '.') }} M.
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Claúsula
	            		</span>
	            		<div class="data">
	                        {{ number_format($player->price, 2, ',', '.') }} M.
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix border-0">
	            		<span class="title">
	            			<i class="fab fa-gratipay mr-1 {{ num_favourite_player($player->id) > 0 ? 'text-danger' : '' }}"></i>Seguidores
	            		</span>
	            		<div class="data">
	                        {{ number_format(num_favourite_player($player->id), 0, ',', '.') }}
	            		</div>
	            	</div>

	            	<div class="detail-item clearfix mt-3">
	            		<span class="title">
	            			Nacionalidad
	            		</span>
	            		<div class="data">
	                        @if ($player->player->nation_name)
	                        	<img src="{{ asset($player->player->nation_flag()) }}" width="24" class="mr-2">
	                            {{ $player->player->nation_name }}
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Edad
	            		</span>
	            		<div class="data">
	                        @if ($player->player->age)
	                            {{ $player->player->age }} años
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Altura
	            		</span>
	            		<div class="data">
	                        @if ($player->player->height)
	                            {{ $player->player->height }} cm
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			Pie
	            		</span>
	            		<div class="data">
	                        @if ($player->player->foot)
	                            {{ $player->player->foot == 'right' ? 'Derecho' : 'Izquierdo' }}
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix">
	            		<span class="title">
	            			*Equipo
	            		</span>
	            		<div class="data">
	                        @if ($player->player->team_name)
	                            {{ $player->player->team_name }}
	                        @endif
	            		</div>
	            	</div>
	            	<div class="detail-item clearfix border-0">
	            		<span class="title">
	            			*Liga
	            		</span>
	            		<div class="data">
	                        @if ($player->player->league_name)
	                            {{ $player->player->league_name }}
	                        @endif
	            		</div>
	            	</div>
	        	</div>
	        </div>
		</div>
	</div>

    <div class="modal-bottom">
        @if ($player->player->game_id)
            <a target="_blank" href="{{ $player->player->fifaindex_link() }}">+info en fifaindex.com</a>
            {{-- <a target="_blank" href="{{ $player->player->pesmaster2020_link() }}">+info en pesmaster.com</a> --}}
        @endif
    </div>

</div>