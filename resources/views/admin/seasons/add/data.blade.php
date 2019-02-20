<form
    id="frmAdd"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.seasons.save') }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ csrf_field() }}

    <div class="table-form-content col-12 col-lg-8 col-xl-6 p-md-3 animated fadeIn">
        <div class="form-group row pt-2">
            <label for="name" class="col-sm-3 col-form-label">Nombre</label>
            <div class="col-sm-9">
                <input type="text" class="form-control {{ $errors->first('name') ? 'invalid' : '' }}" id="name" name="name" placeholder="Nombre" autofocus value="{{ old('name') }}">
                @if ($errors->first('name'))
                    <small class="text-danger">{{ $errors->first('name') }}</small>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label for="num_participants" class="col-sm-3 col-form-label">Participantes</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="num_participants" name="num_participants" placeholder="Número de participantes" autofocus value="{{ old('num_participants') }}">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="participant_has_team" name="participant_has_team" checked>
                    <label class="custom-control-label is-valid" for="participant_has_team">
                        <span>Cada participante representa un equipo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="use_rosters" name="use_rosters" checked>
                    <label class="custom-control-label is-valid" for="use_rosters">
                        <span>Usar plantillas de jugadores</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="use_economy" name="use_economy" checked>
                    <label class="custom-control-label is-valid" for="use_economy">
                        <span>Usar economía</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="initial_budget" class="col-sm-3 col-form-label">Presupuesto</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="initial_budget" name="initial_budget" placeholder="Presupuesto inicial" autofocus value="{{ old('initial_budget') }}">
            </div>
        </div>

    </div>

    <div class="table-form-footer col-12 col-lg-8 col-xl-6 pt-3 px-3 px-md-0">
        <input type="submit" class="btn btn-primary border border-primary" value="Guardar" id="btnSave">
        <div class="no-close custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="no_close" name="no_close">
            <label class="custom-control-label is-valid" for="no_close">Insertar nuevo registro</label>
        </div>
    </div>

</form>