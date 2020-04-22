<form
    id="frmEdit"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.seasons.update', $season->id) }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ method_field('PUT') }}
    {{ csrf_field() }}

    <div class="table-form-content col-12 col-lg-8 col-xl-6 p-md-3 animated fadeIn">
        <div class="form-group row pt-2">
            <label for="name" class="col-sm-3 col-form-label">Nombre</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" value="{{ old('name', $season->name) }}" autofocus>
                @if ($errors->first('name'))
                    <small class="text-danger">{{ $errors->first('name') }}</small>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="num_participants" class="col-sm-3 col-form-label">Participantes</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="num_participants" name="num_participants" placeholder="Número de participantes" autofocus value="{{ old('num_participants', $season->num_participants) }}">
                <small class="text-info">
                    <i class="fas fa-info mr-1"></i>Los participantes se crearán / eliminarán automaticamente
                </small>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="participant_has_team" name="participant_has_team" {{ $season->participant_has_team ? 'checked = "checked"' : ''}}>
                    <label class="custom-control-label is-valid" for="participant_has_team">
                        <span>Cada participante representa un equipo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="use_rosters" name="use_rosters" {{ $season->use_rosters ? 'checked = "checked"' : ''}}>
                    <label class="custom-control-label is-valid" for="use_rosters">
                        <span>Usar plantillas de jugadores</span>
                    </label>
                </div>
                <small class="{{ $season->use_rosters ? 'd-block' : 'd-none'}} text-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Al desmarcar, los jugadores existentes se eliminarán automaticamente
                </small>
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="players_db_id" class="col-form-label">Player Database</label>
                <select class="selectpicker form-control" name="players_db_id" id="players_db_id" data-size="3" data-live-search="true">
                    <option value="0">Ninguna</option>
                    @foreach ($databases as $database)
                        @if ($database->id == $season->players_db_id)
                            <option selected value="{{ $database->id }}">{{ $database->name }}</option>
                        @else
                            <option value="{{ $database->id }}">{{ $database->name }}</option>
                        @endif
                    @endforeach
                </select>
                <small class="text-warning">
                    Al cambiar, los jugadores existentes se eliminarán y se importarán los de la database seleccionada
                </small>
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="initial_budget">Mínimo jugadores por plantilla</label>
                <input type="number" class="form-control" id="min_players" name="min_players" placeholder="Número mínimo de jugadores por plantilla" autofocus value="{{ old('min_players', $season->min_players) }}">
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="initial_budget">Máximo jugadores por plantilla</label>
                <input type="number" class="form-control" id="max_players" name="max_players" placeholder="Número máximo de jugadores por plantilla" autofocus value="{{ old('max_players', $season->max_players) }}">
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="salary_cap">Tope salarial de la plantilla</label>
                <input type="number" class="form-control" id="salary_cap" name="salary_cap" placeholder="Tope salarial de la plantilla" autofocus value="{{ old('salary_cap', $season->salary_cap) }}">
                <small class="text-info">
                    <i class="fas fa-info mr-1"></i>Tope salarial permitido en el periodo de edición de salarios
                </small>
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="free_players_salary">Salario jugadores libres</label>
                <input type="number" step="0.5" min="0.5" class="form-control" id="free_players_salary" name="free_players_salary" placeholder="Salario jugadores libres" autofocus value="{{ old('free_players_salary', $season->free_players_salary) }}">
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="free_players_new_salary">Salario jugadores libres fichados</label>
                <input type="number" step="0.5" min="0.5" class="form-control" id="free_players_new_salary" name="free_players_new_salary" placeholder="Salario jugadores libres fichados" autofocus value="{{ old('free_players_new_salary', $season->free_players_new_salary) }}">
                <small class="text-info">
                    <i class="fas fa-info mr-1"></i>Nuevo salario de los jugadores libres una vez son fichados
                </small>
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="free_players_cost">Coste de los jugadores libres</label>
                <input type="number" step="0.5" min="0.5" class="form-control" id="free_players_cost" name="free_players_cost" placeholder="Coste de los jugadores libres" autofocus value="{{ old('free_players_cost', $season->free_players_cost) }}">
            </div>

        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="free_players_remuneration">Remuneración al liberar jugador</label>
                <input type="number" step="0.5" min="0.5" class="form-control" id="free_players_remuneration" name="free_players_remuneration" placeholder="Remuneración al liberar jugador" autofocus value="{{ old('free_players_remuneration', $season->free_players_remuneration) }}">
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="max_clauses_paid">Máximo claúsulas pagadas permitido</label>
                <input type="number" min="0" class="form-control" id="max_clauses_paid" name="max_clauses_paid" placeholder="Máximo claúsulas pagadas permitido" autofocus value="{{ old('max_clauses_paid', $season->max_clauses_paid) }}">
            </div>
        </div>
        <div class="form-group row roster_options {{ $season->use_rosters ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="max_clauses_received">Máximo claúsulas recibidas permitido</label>
                <input type="number" min="0" class="form-control" id="max_clauses_received" name="max_clauses_received" placeholder="Máximo claúsulas recibidas permitido" autofocus value="{{ old('max_clauses_received', $season->max_clauses_received) }}">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="use_economy" name="use_economy" {{ $season->use_economy ? 'checked = "checked"' : ''}}>
                    <label class="custom-control-label is-valid" for="use_economy">
                        <span>Usar economía</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row initial_budget {{ $season->use_economy ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <label for="initial_budget">Presupuesto inicial</label>
                <input type="number" class="form-control" id="initial_budget" name="initial_budget" placeholder="Presupuesto inicial" autofocus value="{{ old('initial_budget', $season->initial_budget) }}">
            </div>
        </div>
        <div class="form-group row salaries_paid {{ $season->use_economy ? 'd-block' : 'd-none'}}">
            <div class="col-sm-9 offset-sm-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="salaries_paid" name="salaries_paid" {{ $season->salaries_paid ? 'checked = "checked"' : ''}}>
                    <label class="custom-control-label is-valid" for="salaries_paid">
                        <span>Salarios pagados</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12 border-top">
                <div class="pt-3">
                    <label for="rules py-3">Reglas</label>
                </div>
                <textarea class="form-control summernote" name="rules" id="rules" rows="30" value="{!! old('rules', $season->rules) !!}"></textarea>
            </div>
        </div>
    </div>


    <div class="table-form-footer col-12 col-lg-8 col-xl-6 pt-3 px-3 px-md-0">
        <input type="submit" class="btn btn-primary border border-primary" value="Guardar" id="btnSave">
        <div class="d-inline-block ml-3">
            <img id="loading" class="d-none" src="{{ asset('img/loading.gif') }}" alt="" width="48">
        </div>
{{--         <div class="d-block">
            <small id="loading-info" class="d-none">Importando los jugadores de la database a la temporada</small>
        </div>    --}}
    </div>

</form>