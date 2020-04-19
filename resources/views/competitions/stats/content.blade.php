@include('competitions.stats.team_selector')

<div class="container">

	@if ($participant_id > 0)
		<div class="row justify-content-center">
			<div class="col-12 col-md-10 col-lg-8 px-3 px-md-0 py-3 animated fadeIn">
				<div class="d-inline-block align-middle">
					<figure class="bg-white border rounded-circle m-0 shadow" style="padding: 10px">
						<img src="{{ $participant->logo() }}" width="40">
					</figure>
				</div>
				<div class="d-inline-block align-middle pl-2">
					<strong>{{ $participant->name() }}</strong>
					<small class="text-muted d-block">
						{{ $participant->sub_name() }}
					</small>
				</div>
			</div>
		</div>
	@endif

	@if ($competition->stats_goals)
		@include('competitions.stats.goals')
	@endif

	@if ($competition->stats_assists)
		@include('competitions.stats.assists')
	@endif

	@if ($competition->stats_yellow_cards)
		@include('competitions.stats.yellow_cards')
	@endif

	@if ($competition->stats_red_cards)
		@include('competitions.stats.red_cards')
	@endif

</div>