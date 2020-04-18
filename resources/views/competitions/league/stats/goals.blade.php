@if ($stats_goals->count() > 0 && $participant_id == 0)
	<div class="row justify-content-center">
		<div class="col-12 col-md-10 col-lg-8 px-3 px-md-0 py-3">
			<div class="clearfix">
				<div class="float-left animated jello">
					<div class="d-inline-block align-middle">
						<figure class="bg-white border rounded-circle m-0 shadow" style="padding: 10px">
							<img src="{{ $stats_goals->first()->player->player->getImgFormatted() }}" width="40">
						</figure>
					</div>
					<div class="d-inline-block align-middle pl-2">
						<strong>{{ $stats_goals->first()->player->player->name }}</strong>
						<small class="text-muted d-block">
							{{ $stats_goals->first()->player->participant ? $stats_goals->first()->player->participant->name() : '' }}
						</small>
					</div>
				</div>
				<div class="float-right text-center animated bounceInRight delay-2s">
					<img src="{{ asset('img/competitions/golden_boot.png') }}" alt="" width="40">
					<div style="font-size: .7em; font-weight: bold; text-transform: uppercase; padding-top: 4px">
						{{ $stats_goals->first()->goals }} goles
					</div>
				</div>
			</div>
		</div>
	</div>
@endif

@if ($stats_goals->count() > 0 && $participant_id > 0)
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
<div class="row justify-content-center">
	<div class="col-12 col-md-10 col-lg-8 px-0 py-3">
		<div class="px-3 px-md-0 pb-2">
			<strong class="text-uppercase" style="font-size: .9em">Clasificación Goleadores</strong>
		</div>
		@if ($stats_goals->count() > 0)
			<table class="stats">
				<tbody>
				@foreach ($stats_goals as $stat)
					<tr class="item" data-id="{{ 'goals'.$stat->player_id }}">
						<td class="pos">
							{{ $loop->iteration }}
						</td>
						<td class="player-img">
							<img src="{{ $stat->player->player->getImgFormatted() }}">
						</td>
						<td class="player-name">
							{{ $stat->player->player->name }}
							@if ($stats_goals->first()->player->participant)
								<small class="d-block">
									@if ($participant_id == 0)
										<img src="{{ $stat->player->participant->logo() }}" width="16">
										<span class="text-muted">{{ $stat->player->participant->name() }}</span>
									@else
										<img src="{{ asset($stat->player->player->nation_flag()) }}" width="16">
										<span class="text-muted">{{ $stat->player->player->nation_name }}</span>
										<span class="text-muted">, {{ $stat->player->player->age }} años</span>
										<span class="text-muted"> - {{ $stat->player->player->position }}</span>
									@endif
								</small>
							@endif
						</td>
						<td class="total">
							{{ $stat->goals }}
						</td>
					</tr>
					<tr class="detail d-none animated" id="{{ 'goals'.$stat->player_id }}">
						<td colspan="4">
							@foreach ($stat->stat_detail('goals', $competition->id, $stat->player->id) as $detail)
								<div class="list clearfix text-muted">
									<div class="d-inline-block float-left" style="width: 70px">
										Jornada {{ $detail->match->day->order }}
									</div>
									<div class="d-inline-block float-left">
										{{ $detail->match->match_result() }}
									</div>
									<div class="d-inline-block float-right text-right">
										@for ($i = 0; $i < $detail->goals; $i++)
										    <i class="fas fa-futbol"></i>
										@endfor
									</div>
								</div>
							@endforeach
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p class="mx-3">No hay datos registrados</p>
		@endif
	</div>
</div>