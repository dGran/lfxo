<div class="title-position">
	<div class="container clearfix">
		<h4>Porteros</h4>
		<img src="{{ asset('img/clubs/pt.png') }}">
	</div>
</div>

<div class="container">
	<div class="row m-0">
		@foreach ($participant->players as $player)
			@if ($player->player->position == 'PO')
				@include('clubs.roster.card_data')
			@endif
		@endforeach
	</div>
</div>

<div class="title-position">
	<div class="container clearfix">
		<h4>Defensas</h4>
		<img src="{{ asset('img/clubs/ct.png') }}">
	</div>
</div>
<div class="container">
	<div class="row m-0">
		@foreach ($participant->players as $player)
			@if ($player->player->position == 'DFC' || $player->player->position == 'LD' || $player->player->position == 'LI' || $player->player->position == 'CAD' || $player->player->position == 'CAI')
				@include('clubs.roster.card_data')
			@endif
		@endforeach
	</div>
</div>

<div class="title-position">
	<div class="container clearfix">
		<h4>Medios</h4>
		<img src="{{ asset('img/clubs/mc.png') }}">
	</div>
</div>
<div class="container">
	<div class="row m-0">
		@foreach ($participant->players as $player)
			@if ($player->player->position == 'MCD' || $player->player->position == 'MC' || $player->player->position == 'MCO' || $player->player->position == 'MI' || $player->player->position == 'MD')
				@include('clubs.roster.card_data')
			@endif
		@endforeach
	</div>
</div>

<div class="title-position">
	<div class="container clearfix">
		<h4>Delanteros</h4>
		<img src="{{ asset('img/clubs/dc.png') }}">
	</div>
</div>
<div class="container">
	<div class="row m-0">
		@foreach ($participant->players as $player)
			@if ($player->player->position == 'DC' || $player->player->position == 'SD' || $player->player->position == 'EI' || $player->player->position == 'ED')
				@include('clubs.roster.card_data')
			@endif
		@endforeach
	</div>
</div>