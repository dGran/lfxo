<div class="row m-0">
    <div class="col-12">
        <div class="py-3 p-md-0 pt-md-3">
            @if ($playoff->rounds->count() == 0)
                <h5><strong>Configuración</strong></h5>

                <form
                    lang="{{ app()->getLocale() }}"
                    role="form"
                    method="POST"
                    action="{{ route('admin.season_competitions_phases_groups_playoffs.update', $playoff->id) }}"
                    enctype="multipart/form-data"
                    data-toggle="validator"
                    autocomplete="off">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}


                    <div class="form-group row">
                        <div class="col-12 col-md-6">
                            <label for="predefined_rounds">Cuadro de eliminatorias predefinido</label>
                            <select class="selectpicker form-control" name="predefined_rounds" id="predefined_rounds">
                                <option {{ !$playoff->predefined_rounds ? 'selected' : ''}} value="0">No (sorteo en cada ronda)</option>
                                <option {{ $playoff->predefined_rounds ? 'selected' : ''}} value="1">Sí</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-12">
                            <input type="submit" class="btn btn-primary" value="Guardar cambios">
                        </div>
                    </div>

                </form>
            @else

                La configuración del playoff no se puede modificar ya que existen rondas. Para modificar la configuración debes resetear previamente las rondas.
            @endif


        </div>

        @if ($group->participants->count() == $group->num_participants)
            <div class="pb-3">
                <h5 class="py-3 m-0 border-top"><strong>Rondas</strong></h5>
                @if ($playoff->rounds->count() == 0)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type_round" id="unique_round" value="1">
                        <label class="form-check-label" for="unique_round">
                            Ronda única
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type_round" id="complete_playoff" value="0" checked>
                        <label class="form-check-label" for="complete_playoff">
                            Playoff completo
                        </label>
                    </div>
                    <a href="" id="btnGenerateRounds" class="btn btn-primary mt-3" data-id={{ $playoff->id }}>
                        Generar rondas
                    </a>
                @else
                    <a href="" id="btnResetRounds" class="btn btn-danger" data-id={{ $playoff->id }}>
                        Resetear todas las rondas
                    </a>
                @endif
            </div>
        @else
            <h5 class="py-3 text-center text-danger animated flash infinite text-uppercase">Lista de participantes no completada</h5>
        @endif
    </div>
</div>


<div class="table-form-content col-12 animated fadeIn p-0 border-0">
    @if (!$playoff->rounds)
        <div class="text-center border-top py-4">
            <figure>
                <img src="{{ asset('img/table-empty.png') }}" alt="" width="72">
            </figure>
            Actualmente no existen rondas
        </div>
    @else
        <table class="table calendar">
{{--            <colgroup>
                <col width="50%" />
                <col width="0%" />
                <col width="0%" />
                <col width="0%" />
                <col width="50%" />
            </colgroup> --}}
            @foreach ($playoff->rounds as $round)
                <tr class="days border-top border-bottom">
                    <td colspan="6" class="p-2">
                        <strong class="text-uppercase float-left">{{ $round->name }}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="p-0">
                        <form
                            id="frmUpdate{{ $round->id }}"
                            lang="{{ app()->getLocale() }}"
                            role="form"
                            method="POST"
                            action="{{ route('admin.season_competitions_phases_groups_playoffs.round.update', $round->id) }}"
                            enctype="multipart/form-data"
                            data-toggle="validator"
                            autocomplete="off">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}

                            <div class="table-form-content pt-3 px-3 my-2">

                                <div class="form-group row">
                                    <div class="col-6 col-lg-4">
                                        <label for="name{{ $round->id }}">Nombre de la ronda</label>
                                        <input type="text" class="form-control" id="name{{ $round->id }}" name="name" placeholder="Nombre de la ronda" value="{{ old('name', $round->name) }}">
                                    </div>
                                    <div class="col-6 col-lg-4">
                                        <label for="playoff_type{{ $round->id }}">Tipo de eliminatoria</label>
                                        <select class="selectpicker form-control" name="playoff_type" id="playoff_type{{ $round->id }}" {{ $round->exists_matches() ? 'disabled' : '' }}>
                                            <option {{ !$round->round_trip ? 'selected' : ''}} value="0">Partido único</option>
                                            <option {{ $round->round_trip && !$round->double_value ? 'selected' : ''}} value="1">Ida y vuelta</option>
                                            <option {{ $round->round_trip && $round->double_value ? 'selected' : ''}} value="2">Ida y vuelta (valor doble goles)</option>
                                        </select>
                                    </div>

                                    <div class="col-6 col-lg-4 mt-3 mt-lg-0">
                                        <label for="date_limit{{ $round->id }}">Fecha límite</label>
                                        <input type="datetime-local" class="form-control" name="date_limit" id="date_limit{{ $round->id }}" value="{{ $round->date_limit ? $round->getDateLimit_date() . 'T' . $round->getDateLimit_time() : '' }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-6 col-lg-4">
                                        <label for="play_amount{{ $round->id }}"><i class="fas fa-euro-sign mr-2"></i>por jugar</label>
                                        <input type="number" class="form-control" id="play_amount{{ $round->id }}" name="play_amount" placeholder="Ganancias por jugar" min="0" step=".5" value="{{ old('play_amount', $round ? $round->play_amount : 1) }}" {{ $round->exists_matches() ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6 col-lg-4 mt-3 mt-lg-0">
                                        <label for="play_ontime_amount{{ $round->id }}"><i class="fas fa-euro-sign mr-2"></i>por jugar en plazo</label>
                                        <input type="number" class="form-control" id="play_ontime_amount{{ $round->id }}" name="play_ontime_amount" placeholder="Ganancias por jugar en plazo" min="0" step=".5" value="{{ old('play_ontime_amount', $round ? $round->play_ontime_amount : 0) }}" {{ $round->exists_matches() ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6 col-lg-4">
                                        <label for="win_amount{{ $round->id }}"><i class="fas fa-euro-sign mr-2"></i>por victoria</label>
                                        <input type="number" class="form-control" id="win_amount{{ $round->id }}" name="win_amount" placeholder="Ganancias por victoria" min="0" step=".5" value="{{ old('win_amount', $round ? $round->win_amount : 3) }}" {{ $round->exists_matches() ? 'disabled' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        <input type="submit" class="btn btn-primary" value="Guardar cambios">
                                        @if ($round->exists_matches())
                                            <small class="text-info d-block pt-1">
                                                Hay campos deshabilitados ya que existen partidos en la ronda
                                            </small>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

    @endif
</div>