<div class="row justify-content-center">
	<div class="col-12 col-md-10 col-lg-8 px-0 py-3">
		<div class="px-3 px-md-0 pb-2">
			<strong class="text-uppercase" style="font-size: .9em">Tarjetas rojas</strong>
		</div>
		@if ($stats_red_cards->count() > 0)
			<table class="stats">
				<tbody>
				@foreach ($stats_red_cards as $stat)
					<tr class="item" data-id="{{ 'red_cards'.$stat->player_id }}">
						<td class="pos">
							{{ $loop->iteration }}
						</td>
						<td class="player-img">
							<img src="{{ $stat->player->player->getImgFormatted() }}">
						</td>
						<td class="player-name">
							{{ $stat->player->player->name }}
							@if ($stats_red_cards->first()->player->participant)
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
							{{ $stat->red_cards }}
						</td>
					</tr>
					<tr class="detail d-none animated" id="{{ 'red_cards'.$stat->player_id }}">
						<td colspan="4">
							@foreach ($stat->stat_detail('red_cards', $competition->id, $stat->player->id) as $detail)
								<div class="list clearfix">
									<div class="d-inline-block float-left">
										<span class="text-muted">
											@if ($detail->match->day)
												@if ($detail->match->day->league->group->phase->groups->count() > 1)
													{{ $detail->match->day->league->group->name }} -
												@endif
												Jornada {{ $detail->match->day->order }}
											@else
												{{ $detail->match->clash->round->name }}
												@if ($detail->match->clash->round->round_trip)
													@if ($detail->match->order == 1)
														<span>- Ida</span>
													@else
														<span>- Vuelta</span>
													@endif
												@endif
											@endif
										</span>
										<span class="d-block">
											<i class="fas fa-caret-right ml-2 mr-1"></i>
											{{ $detail->match->match_result() }}
										</span>
									</div>
									<div class="d-inline-block float-right text-right">
										<br>
										@for ($i = 0; $i < $detail->red_cards; $i++)
										    <i class="icon-soccer-card text-danger"></i>
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