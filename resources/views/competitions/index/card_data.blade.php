<div class="col-12 col-lg-4">
	<a class="text-dark {{ !$competition->initialPhase()->active ? 'disabled' : '' }}" href="{{ route('competitions.table', [$season_slug, $competition->slug]) }}">
		<div class="competition-item shadow-sm">
			<div class="logo">
				<img src="{{ $competition->getImgFormatted() }}">
			</div>
			<div class="links">
				<h5>{{ $competition->name }}</h5>
				<ul>
					<li>
						<a class="{{ !$competition->initialPhase()->active ? 'disabled' : '' }}" href="{{ route('competitions.table', [$season_slug, $competition->slug]) }}">
							<i class="fas fa-caret-right mr-1"></i>
							{{ $competition->initialPhase()->mode == 'league' ? 'Clasificación' : 'PlayOffs' }}
						</a>
					</li>
					<li>
						<a class="{{ !$competition->initialPhase()->active ? 'disabled' : '' }}" href="{{ route('competitions.calendar', [$season_slug, $competition->slug]) }}">
							<i class="fas fa-caret-right mr-1"></i>
							Partidos
						</a>
					</li>
					<li>
						@if ($competition->has_stats())
							<a class="{{ !$competition->initialPhase()->active ? 'disabled' : '' }}" href="{{ route('competitions.stats', [$season_slug, $competition->slug]) }}">
								<i class="fas fa-caret-right mr-1"></i>
								Estadísticas
							<a>
						@endif
					</li>
				</ul>
			</div>
		</div>
	</a>
</div>