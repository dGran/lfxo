@if ($group->phase->mode == 'league') {{-- league --}}
	<div class="scrolling-wrapper">
		<ul>
			<li class="item {{ \Route::current()->getName() == 'competitions.table' ? 'active' : '' }}">
				<a href="{{ route('competitions.table', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
					<i class="fas fa-th-list"></i>
					<span>Clasificación</span>
				</a>
			</li>

			<li class="item {{ \Route::current()->getName() == 'competitions.calendar' ? 'active' : '' }}">
				<a href="{{ route('competitions.calendar', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
					<i class="icon-soccer-ball"></i>
					<span>Partidos</span>
				</a>
			</li>

			@if ($league->has_stats())
				<li class="item {{ \Route::current()->getName() == 'competitions.stats' ? 'active' : '' }}">
					<a href="{{ route('competitions.stats', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
						<i class="fas fa-chart-bar"></i>
						<span>Estadísticas</span>
					</a>
				</li>
			@endif
		</ul>
	</div>
@else {{-- playoffs --}}
	<div class="scrolling-wrapper">
		<ul>
			<li class="item {{ \Route::current()->getName() == 'competitions.table' ? 'active' : '' }}">
				<a href="{{ route('competitions.table', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
					<i class="icon-playoff"></i>
					<span>Playoffs</span>
				</a>
			</li>

			<li class="item {{ \Route::current()->getName() == 'competitions.calendar' ? 'active' : '' }}">
				<a href="{{ route('competitions.calendar', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
					<i class="icon-soccer-ball"></i>
					<span>Partidos</span>
				</a>
			</li>

			@if ($playoff->has_stats())
				<li class="item {{ \Route::current()->getName() == 'competitions.stats' ? 'active' : '' }}">
					<a href="{{ route('competitions.stats', [$competition->season->slug, $group->phase->competition->slug, $group->phase_slug_if_necesary(), $group->group_slug_if_necesary()]) }}">
						<i class="fas fa-chart-bar"></i>
						<span>Estadísticas</span>
					</a>
				</li>
			@endif
		</ul>
	</div>
@endif