@if ($seasons->count()>1)
	<div class="season-selector">
		<div class="container px-3">
			<label for="season_selector">Temporada</label>
			<select class="selectpicker btn-light" id="season_selector">
				@foreach ($seasons as $season)
					<option {{ $season->slug == $season_slug ? 'selected' : '' }} value="{{ route('clubs', $season->slug) }}">
						<span>{{ $season->name }}</span>
						@if ($season->id == active_season()->id)
							<small>(activa)</small>
						@endif
					</option>
				@endforeach
			</select>
		</div>
	</div>
@endif

<div class="container">
    <div class="row" style="padding-bottom: 15px">
		@foreach ($participants as $participant)
			@if ($participant->team)
				<div class="col-12 col-md-6 col-lg-4">
					<div class="club-card">
						<div class="text-center d-table-cell" style="width: 170px">
							<a class="text-dark" href="{{route('club', [$season_slug, $participant->team->slug]) }}">
								<img src="{{ $participant->logo() }}" alt="" width="72px">
								<span class="d-block mt-1" style="font-size: .9em; font-weight: bold">{{ $participant->name() }}</span>
								<span class="d-block" style="font-size: .8em;">{{ $participant->sub_name() }}</span>
							</a>
						</div>
						<div class="d-table-cell border-left align-top ">
							<ul>
								<li>
									<a class="text-dark" href="{{route('club', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Club
									</a>
								</li>
								<li>
									<a class="text-dark" href="{{route('club.roster', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Plantilla
									</a>
								</li>
								<li>
									<a class="text-dark" href="{{route('club.economy', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Economía
									</a>
								</li>
								<li>
									<a class="text-dark" href="{{route('club.calendar', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Calendario
									</a>
								</li>
								<li>
									<a class="text-dark" href="{{route('club.pending_matches', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Pendientes
									</a>
								</li>
								<li>
									<a class="text-dark" href="{{route('club.press', [$season_slug, $participant->team->slug]) }}">
										<i class="fas fa-caret-right mr-1" style="color: #89be38"></i>
										Prensa
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			@endif
		@endforeach
    </div>

</div>
