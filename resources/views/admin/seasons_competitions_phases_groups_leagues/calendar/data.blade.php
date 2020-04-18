@if ($group->participants->count() == $group->num_participants)
	<div class="table-form-content px-3 col-12 mb-0 mb-md-2 animated fadeIn">
		<form
		    id="frmGenerate"
		    lang="{{ app()->getLocale() }}"
		    role="form"
		    method="POST"
		    action="{{ route('admin.season_competitions_phases_groups_leagues.calendar.generate', [$group->phase->competition->slug, $group->phase->slug, $group->slug]) }}"
		    enctype="multipart/form-data"
		    data-toggle="validator"
		    autocomplete="off">
		    {{ csrf_field() }}

			<div class="form-group row pt-2">
				<div class="col-12">
				    <div class="custom-control custom-checkbox">
				        <input type="checkbox" class="custom-control-input" id="second_round" name="second_round">
				        <label class="custom-control-label" for="second_round">Partidos de ida y vuelta</label>
				    </div>
				    <div class="custom-control custom-checkbox">
				        <input type="checkbox" class="custom-control-input" id="inverse_order" name="inverse_order" disabled>
				        <label class="custom-control-label text-muted" id="lbinverse_order" for="inverse_order">Orden inverso para la segunda vuelta</label>
				    </div>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-12">
					<a id="btnGenerate" href="" class="btn btn-primary border border-primary">
			    		<i class="fas fa-magic mr-2"></i>
			    		Generar calendario
					</a>
			    </div>
			</div>

		</form>
	</div>
@else
	<h5 class="py-3 text-center text-danger animated flash infinite text-uppercase">Lista de participantes no completada</h5>
@endif

<div class="table-form-content col-12 animated fadeIn p-0 border-0">
	@if ($league->days->count() == 0)
	    <div class="text-center border-top py-4">
            <figure>
                <img src="{{ asset('img/table-empty.png') }}" alt="" width="72">
            </figure>
            Actualmente no existen partidos
	    </div>
	@else
	    <table class="table calendar">
{{-- 	        <colgroup>
	        	<col width="50%" />
	        	<col width="0%" />
	        	<col width="0%" />
	        	<col width="0%" />
	            <col width="50%" />
	        </colgroup> --}}
			@foreach ($league->days as $day)
				<tr class="days border" data-id="{{ $day->id }}">
					<td colspan="6" class="p-2">
						<div class="clearfix">
							<div class="float-left">
								<i class="fas fa-circle mr-2 {{ $day->active ? 'text-success' : 'text-secondary' }}"></i><strong class="text-uppercase">Jornada {{ $day->order }}</strong>
								@if ($day->active)
									<a href="{{ route('admin.season_competitions_phases_groups_leagues.calendar.day.desactivate', $day->id) }}" class="d-block pt-1">
										<small>Desactivar jornada</small>
									</a>
								@else
									<a href="{{ route('admin.season_competitions_phases_groups_leagues.calendar.day.activate', $day->id) }}" class="d-block pt-1">
										<small>Activar jornada</small>
									</a>
								@endif
							</div>
							<div class="float-right text-right">
								@if ($day->date_limit)
									<small class="text-muted">
										<strong>Fecha límite: </strong>{{ \Carbon\Carbon::parse($day->date_limit)->format('j M, H:i') }}
									</small>
								@else
									<small class="text-muted">
										Plazo no establecido
									</small>
								@endif
				        		<a href="" data-toggle="modal" data-target="#dayLimitModal" class="d-block pt-1">
					        		<small>
					        			Editar fecha límite
					        		</small>
				        		</a>
							</div>
						</div>
					</td>
				</tr>
			    @foreach ($day->matches as $match)
			    	<tr class="matches" data-id="{{ $match->id }}" data-name="{{ $match->local_participant->participant->name() . ' ' . $match->local_score . '-' . $match->visitor_score . ' ' . $match->visitor_participant->participant->name() }}">
				        <td class="text-right">
                            <span class="text-uppercase {{ $match->sanctioned_id && $match->local_id == $match->sanctioned_id ? 'text-danger' : '' }}">{{ $match->local_participant->participant->name() == 'undefined' ? '' : $match->local_participant->participant->name() }}</span>
                            @if (($match->sanctioned_id) && ($match->local_id == $match->sanctioned_id))
                            	<i class="fas fa-exclamation ml-1 text-danger"></i>
                            @endif
                            <small class="text-black-50 d-block">
                                @if ($match->local_participant->participant->sub_name() == 'undefined')
                                    <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
                                @else
                                    {{ $match->local_participant->participant->sub_name() }}
                                @endif
                            </small>
				        </td>
                        <td class="img text-right" width="32">
                            <img src="{{ $match->local_participant->participant->logo() }}" alt="">
                        </td>
				        <td class="score text-center" width="90">
				        	@if (is_null($match->local_score) && is_null($match->visitor_score))
				        		<a href="" data-toggle="modal" data-target="#updateModal">
					        		<small class="bg-primary rounded px-3 py-1 text-white">
					        			EDITAR
					        		</small>
				        		</a>
				        	@else
								<span class="bg-light rounded px-3 py-1 {{ $match->sanctioned_id ? 'border text-danger' : '' }}">
									{{ $match->local_score }} - {{ $match->visitor_score }}
								</span>
				        	@endif
				        </td>
                        <td class="img text-left" width="32">
                            <img src="{{ $match->visitor_participant->participant->logo() }}" alt="">
                        </td>
				        <td class="text-left">
                            @if (($match->sanctioned_id) && ($match->visitor_id == $match->sanctioned_id))
                            	<i class="fas fa-exclamation mr-1 text-danger"></i>
                            @endif
                            <span class="text-uppercase {{ $match->sanctioned_id && $match->visitor_id == $match->sanctioned_id ? 'text-danger' : '' }}">{{ $match->visitor_participant->participant->name() == 'undefined' ? '' : $match->visitor_participant->participant->name() }}</span>
                            <small class="text-black-50 d-block">
                                @if ($match->visitor_participant->participant->sub_name() == 'undefined')
                                    <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
                                @else
                                    {{ $match->visitor_participant->participant->sub_name() }}
                                @endif
                            </small>
				        </td>
			        </tr>
			        {{-- date limits --}}
					<tr data-id="{{ $match->id }}" data-name="{{ $match->local_participant->participant->name() . ' ' . $match->local_score . '-' . $match->visitor_score . ' ' . $match->visitor_participant->participant->name() }}">
						<td colspan="6" class="text-center">
							@if (!is_null($match->local_score) && !is_null($match->visitor_score))
				        		<a href="{{ route('admin.season_competitions_phases_groups_leagues.reset_match', $match->id) }}" class="btnReset text-danger pb-1 d-block">
					        		<small>
					        			<i class="fas fa-undo-alt mr-1"></i>Resetar {{$match->stadistics}}
					        		</small>
				        		</a>
				        		@if (!$match->sanctioned_id)
					        		<a href="" data-toggle="modal" data-target="#updateStatsModal" class="d-block pb-1">
						        		<small>
						        			Editar estadísticas
						        		</small>
					        		</a>
				        		@endif
				        	@endif
							<small class="text-muted">
								@if ($match->date_limit != $match->day->date_limit)
									<strong>Fecha límite: </strong>{{ \Carbon\Carbon::parse($match->date_limit)->format('j M, H:i') }}
								@else
									Mismo límite de la jornada
								@endif
							</small>
			        		<a href="" data-toggle="modal" data-target="#matchLimitModal" class="d-block pt-1">
				        		<small>
				        			Editar fecha límite
				        		</small>
			        		</a>
						</td>
					</tr>
			    @endforeach
			@endforeach
	    </table>

	@endif
</div>