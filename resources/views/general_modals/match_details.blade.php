<div class="modal-content">
    <div class="modal-header bg-light px-3 py-2" style="font-size: .9em">
    	{{ $match->match_name() }}
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-0">

        <table class="mt-4 mb-2" align="center">
            <tr class="matches">
                <td class="text-right pr-2" width="50%" style="line-height: 1.2em; font-size: .9em">
                    <div class="text-uppercase">
                        <strong>{{ $match->local_participant->participant->name() }}</strong>
                    </div>
                    <small class="text-black-50 d-block">
                        @if ($match->local_participant->participant->sub_name() == 'undefined')
                            <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
                        @else
                            {{ $match->local_participant->participant->sub_name() }}
                        @endif
                    </small>
                </td>
                <td class="img text-center pr-2">
                    <img src="{{ $match->local_participant->participant->logo() }}" alt="" width="32">
                </td>
                <td class="img text-center pl-2">
                    <img src="{{ $match->visitor_participant->participant->logo() }}" alt="" width="32">
                </td>
                <td class="text-left pl-2" width="50%" style="line-height: 1.2em; font-size: .9em">
                    <div class="text-uppercase">
                        <strong>{{ $match->visitor_participant->participant->name()}}</strong>
                    </div>
                    <small class="text-black-50 d-block">
                        @if ($match->visitor_participant->participant->sub_name() == 'undefined')
                            <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
                        @else
                            {{ $match->visitor_participant->participant->sub_name() }}
                        @endif
                    </small>
                </td>
            </tr>

{{--             <tr class="matches">
                <td colspan="9" class="text-center">
                    <h4 class="p-1">
                        <span class="pr-2">{{ $match->local_score }}</span>-<span class="pl-2">{{ $match->visitor_score }}</span>
                    </h4>
                </td>
            </tr> --}}

            <tr class="matches">
                <td></td>
                <td class="text-center">
                    <h4 class="p-2 m-0">
                        <span class="pr-2">{{ $match->local_score }}</span>
                    </h4>
                </td>
                <td class="text-center">
                    <h4 class="p-2 m-0">
                        <span class="pl-2">{{ $match->visitor_score }}</span>
                    </h4>
                </td>
                <td></td>
            </tr>

            <tr class="matches" style="border-top: 1px solid #f2f2f2">
                <td colspan="2" class="text-right pr-3 align-top py-2">
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_local())
                            @if ($stat->goals > 0)
                                @for ($i = 1; $i <= $stat->goals; $i++)
                                    <div style="font-size: .7em">
                                        <span class="pr-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pr-1">
                                        <i class="fas fa-futbol"></i>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_local())
                            @if ($stat->assists > 0)
                                @for ($i = 1; $i <= $stat->assists; $i++)
                                    <div style="font-size: .7em">
                                        <span class="pr-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pr-1">
                                        <i class="icon-soccer-assist"></i>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_local())
                            @if ($stat->yellow_cards > 0)
                                @for ($i = 1; $i <= $stat->yellow_cards; $i++)
                                    <div style="font-size: .7em">
                                        <span class="pr-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pr-1">
                                        <i class="icon-soccer-card text-warning"></i>
                                   </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_local())
                            @if ($stat->red_cards > 0)
                                @for ($i = 1; $i <= $stat->red_cards; $i++)
                                    <div style="font-size: .7em">
                                        <span class="pr-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pr-1">
                                        <i class="icon-soccer-card text-danger"></i>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                </td>

                <td colspan="2" class="pl-3 align-top py-2">
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_visitor())
                            @if ($stat->goals > 0)
                                @for ($i = 1; $i <= $stat->goals; $i++)
                                    <div style="font-size: .7em">
                                        <i class="fas fa-futbol"></i>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pl-1">
                                        <span class="pl-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_visitor())
                            @if ($stat->assists > 0)
                                @for ($i = 1; $i <= $stat->assists; $i++)
                                    <div style="font-size: .7em">
                                        <i class="icon-soccer-assist"></i>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pl-1">
                                        <span class="pl-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_visitor())
                            @if ($stat->yellow_cards > 0)
                                @for ($i = 1; $i <= $stat->yellow_cards; $i++)
                                    <div style="font-size: .7em">
                                        <i class="icon-soccer-card text-warning"></i>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pl-1">
                                        <span class="pl-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                    @foreach ($match->stats as $stat)
                        @if ($stat->is_player_visitor())
                            @if ($stat->red_cards > 0)
                                @for ($i = 1; $i <= $stat->red_cards; $i++)
                                    <div style="font-size: .7em">
                                        <i class="icon-soccer-card text-danger"></i>
                                        <img src="{{ $stat->player->player->getImgFormatted() }}" alt="" width="20" class="pl-1">
                                        <span class="pl-1">
                                            {{ $stat->player->player->name }}
                                        </span>
                                    </div>
                                @endfor
                            @endif
                        @endif
                    @endforeach
                </td>
            </tr>

        </table>


    </div> {{-- modal-body --}}
</div> {{-- modal-content --}}