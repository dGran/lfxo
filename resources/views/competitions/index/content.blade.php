@if ($seasons->count()>1)
	<div class="season-selector">
		<div class="container px-3">
			<label for="season_selector">Temporada</label>
			<select class="selectpicker btn-light" id="season_selector">
				@foreach ($seasons as $season)
					<option {{ $season->slug == $season_slug ? 'selected' : '' }} value="{{ route('competitions', $season->slug) }}">
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
	<div class="row">
		<div class="col-12">
		    <div class="row competitions-list">
				@foreach ($competitions as $competition)
					@if ($competition->isConfigured())
						@include('competitions.index.card_data')
					@endif
				@endforeach
		    </div>
		</div>

{{-- 		<div class="col-12 col-md-6 col-lg-4">
			@include('competitions.index.timeline')
		</div> --}}

	</div>
</div>

