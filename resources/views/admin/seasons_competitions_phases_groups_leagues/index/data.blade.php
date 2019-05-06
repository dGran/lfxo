<form
    id="frmAdd"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.season_competitions_phases_groups_leagues.update_match', [$group->phase->competition->slug, $group->phase->slug, $group->slug, $league->id]) }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ csrf_field() }}

    <div class="table-form-content col-12 col-md-10 col-xl-8 animated fadeIn">

        <div class="form-group row pt-2">
            <div class="col-6 col-lg-3">
                <label for="allow_draws">Empates</label>
                <select class="selectpicker form-control" name="allow_draws" id="allow_draws">
                    @if ($league)
                        @if ($league->allow_draws)
                            <option selected value="1">Sí</option>
                            <option value="0">No</option>
                        @else
                            <option value="1">Sí</option>
                            <option selected value="0">No</option>
                        @endif
                    @else
                        <option selected value="1">Sí</option>
                        <option value="0">No</option>
                    @endif
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <label for="win_points">Puntos por victoria</label>
                <input type="number" class="form-control" id="win_points" name="win_points" placeholder="Puntos por victoria" min="1" step=".5" value="{{ old('win_points', $league ? $league->win_points : 3) }}">
            </div>
            <div class="col-6 col-lg-3 mt-3 mt-lg-0">
                <label for="draw_points">Puntos por empate</label>
                <input type="number" class="form-control" id="draw_points" name="draw_points" placeholder="Puntos por empate" min="0" step=".5" value="{{ old('draw_points', $league ? $league->draw_points : 1) }}">
            </div>
            <div class="col-6 col-lg-3 mt-3 mt-lg-0">
                <label for="play_points">Puntos por derrota</label>
                <input type="number" class="form-control" id="lose_points" name="lose_points" placeholder="Puntos por derrota" min="0" step=".5" value="{{ old('lose_points', $league ? $league->lose_points : 0) }}">
            </div>
        </div>

{{--         <div class="form-group row">
            <div class="col-12 col-lg-9">
                <label for="allow_draws">Orden</label>
                <select class="selectpicker form-control" name="allow_draws" id="allow_draws">
                    <option value="0">Puntos / Gol Average general / Goles a favor</option>
                    <option selected value="1">Puntos / Gol Average particular / Gol Average general / Goles a favor</option>
                </select>
            </div>
        </div> --}}

        <h5 class="py-3 m-0 border-top"><strong>Estadísticas</strong></h5>
        <div class="form-group row">
            <div class="col-6 col-lg-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="stats_mvp" name="stats_mvp" {{ $league && $league->stats_mvp ? 'checked' : '' }}>
                    <label class="custom-control-label" for="stats_mvp">MVP</label>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="stats_goals" name="stats_goals" {{ $league && $league->stats_goals ? 'checked' : '' }}>
                    <label class="custom-control-label" for="stats_goals">Goleadores</label>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="stats_assists" name="stats_assists" {{ $league && $league->stats_assists ? 'checked' : '' }}>
                    <label class="custom-control-label" for="stats_assists">Asistencias</label>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="stats_yellow_cards" name="stats_yellow_cards" {{ $league && $league->stats_yellow_cards ? 'checked' : '' }}>
                    <label class="custom-control-label" for="stats_yellow_cards">Tarjetas Amarillas</label>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="stats_red_cards" name="stats_red_cards" {{ $league && $league->stats_red_cards ? 'checked' : '' }}>
                    <label class="custom-control-label" for="stats_red_cards">Tarjetas Rojas</label>
                </div>
            </div>
        </div>

        @if ($group->phase->competition->season->use_economy)
            <h5 class="py-3 m-0 border-top"><strong>Economía</strong></h5>
            <div class="form-group row">
                <div class="col-6 col-lg-3">
                    <label for="win_amount"><i class="fas fa-euro-sign mr-2"></i>por victoria</label>
                    <input type="number" class="form-control" id="win_amount" name="win_amount" placeholder="Recompensa por victoria" min="0" step=".5" value="{{ old('win_amount', $league ? $league->win_amount : '') }}">
                </div>
                <div class="col-6 col-lg-3">
                    <label for="draw_amount"><i class="fas fa-euro-sign mr-2"></i>por empate</label>
                    <input type="number" class="form-control" id="draw_amount" name="draw_amount" placeholder="Recompensa por empate" min="0" step=".5" value="{{ old('draw_amount', $league ? $league->draw_amount : '') }}">
                </div>
                <div class="col-6 col-lg-3 mt-3 mt-lg-0">
                    <label for="lose_amount"><i class="fas fa-euro-sign mr-2"></i>por derrota</label>
                    <input type="number" class="form-control" id="lose_amount" name="lose_amount" placeholder="Recompensa por derrota" min="0" step=".5" value="{{ old('lose_amount', $league ? $league->lose_amount : '') }}">
                </div>
                <div class="col-6 col-lg-3 mt-3 mt-lg-0">
                    <label for="play_amount"><i class="fas fa-euro-sign mr-2"></i>por jugar</label>
                    <input type="number" class="form-control" id="play_amount" name="play_amount" placeholder="Recompensa por jugar" min="0" step=".5" value="{{ old('play_amount', $league ? $league->play_amount : '') }}">
                </div>
            </div>
        @endif

    </div>

    <div class="table-form-footer col-12 col-lg-8 col-xl-6 pt-3 px-3 px-md-0">
        <input type="submit" class="btn btn-primary border border-primary" value="Guardar" id="btnSave">
    </div>

</form>