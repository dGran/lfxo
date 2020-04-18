@include('competitions.league.stats.team_selector')

<div class="container">

	@if ($competition->stats_goals)
		@include('competitions.league.stats.goals')
	@endif

	@if ($competition->stats_assists)
		@include('competitions.league.stats.assists')
	@endif

	@if ($competition->stats_yellow_cards)
		@include('competitions.league.stats.yellow_cards')
	@endif

	@if ($competition->stats_red_cards)
		@include('competitions.league.stats.red_cards')
	@endif

</div>