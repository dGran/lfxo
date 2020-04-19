<tr class="matches" data-id="{{ $match->id }}" data-name="{{ $match->local_participant->participant->name() . ' ' . $match->local_score . '-' . $match->visitor_score . ' ' . $match->visitor_participant->participant->name() }}">
    <td class="local text-right text-truncate" style="max-width: 95px;">
        <span class="text-uppercase {{ $match->sanctioned_id && $match->local_id == $match->sanctioned_id ? 'text-danger' : '' }}">
            {{ $match->local_participant->participant->name() == 'undefined' ? '' : $match->local_participant->participant->name() }}
        </span>
    	<small class="{{ $match->sanctioned_id && $match->local_id == $match->sanctioned_id ? 'text-danger' : 'text-black-50' }} d-block">
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
    <td class="score text-center" width="70">
    	@if (is_null($match->local_score) && is_null($match->visitor_score))
			@if (!auth()->guest() && (user_is_participant(auth()->user()->id) && (participant_of_user()->id == $match->local_participant->participant->id || participant_of_user()->id == $match->visitor_participant->participant->id)))
                @if ($match->order == 2 && is_null($match->clash->first_match()->local_score) && is_null($match->clash->first_match()->visitor_score))
                Vs
                @else
            		<a href="" data-toggle="modal" data-target="#updateModal">
    	        		<small class="bg-primary rounded px-2 py-1 text-white">
    	        			EDITAR
    	        		</small>
            		</a>
                @endif
    		@else
    		Vs
    		@endif
    	@else
    		@if ($match->sanctioned_id)
				<span class="result rounded px-2 py-1 text-white bg-danger" data-toggle="tooltip" data-placement="top" title="{{ $match->sanctioned_participant->participant->name() }} sancionado">
					{{ $match->local_score }} - {{ $match->visitor_score }}
				</span>
    		@else
                <a href="" data-toggle="modal" data-target="#matchDetailsModal">
                    <span class="result rounded px-2 py-1 {{ $match->sanctioned_id ? 'text-white bg-danger' : '' }}">
                        {{ $match->local_score }} - {{ $match->visitor_score }}
                    </span>
                </a>
    		@endif
    	@endif
    </td>
    <td class="img text-left" width="32">
        <img src="{{ $match->visitor_participant->participant->logo() }}" alt="">
    </td>
    <td class="visitor text-left text-truncate" style="max-width: 95px;">
        <span class="text-uppercase {{ $match->sanctioned_id && $match->visitor_id == $match->sanctioned_id ? 'text-danger' : '' }}">
            {{ $match->visitor_participant->participant->name() == 'undefined' ? '' : $match->visitor_participant->participant->name() }}
        </span>
        <small class="{{ $match->sanctioned_id && $match->visitor_id == $match->sanctioned_id ? 'text-danger' : 'text-black-50' }} d-block">
            @if ($match->visitor_participant->participant->sub_name() == 'undefined')
                <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
            @else
                {{ $match->visitor_participant->participant->sub_name() }}
            @endif
        </small>
    </td>
</tr>
@if ( (!$match->clash->round->round_trip == 1 || ($match->clash->round->round_trip == 1 && $match->order == 2)) && ($match->clash->total_result_local() == $match->clash->total_result_local()) && (!is_null($match->local_score) && !is_null($match->visitor_score)) && (!is_null($match->penalties_local_score) && !is_null($match->penalties_visitor_score)) )
    <tr class="matches">
        <td colspan="5" class="text-center" style="background: none">
            <small class="d-block text-info">
                *{{ $match->clash->winner()->participant->name() }} clasificado por penaltis ({{ $match->penalties_local_score }}-{{ $match->penalties_visitor_score }})
            </small>
        </td>
    </tr>
@endif

@if (!$round->road_trip)
    @if ($round->clashes->count() > $round->num_matches_count())
        <tr class="matches">
            <td colspan="5" class="text-center" style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856402;">
                <small class="text-warning animated flash slower">Pendiente clasificación en rondas anteriores para mostrar el resto de eliminatorias</small>
            </td>
        </tr>
    @endif
@else
    @if ($round->clashes->count() * 2 > $round->num_matches_count())
        <tr class="matches">
            <td colspan="5" class="text-center" style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856402;">
                <small class="text-warning animated flash slower">Pendiente clasificación en rondas anteriores para mostrar el resto de eliminatorias</small>
            </td>
        </tr>
    @endif
@endif