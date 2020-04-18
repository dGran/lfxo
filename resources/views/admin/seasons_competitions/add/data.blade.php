<form
    id="frmAdd"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.season_competitions.save') }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ csrf_field() }}

    <input type="hidden" name="season_id" value="{{ $season->id }}">

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
            <label for="img" class="col-sm-3 col-form-label">Imagen</label>

            <div class="col-sm-9">
                <div class="d-inline-block">
                    <div class="input-group mb-1" id="img_local">
                        <div class="input-group-prepend">
                            <button class="btn btn-danger d-none" type="button" id="img_remove">Eliminar</button>
                        </div>
                         <div class="custom-file">
                            <input readonly type="file" class="custom-file-input" id="img_field" name="img">
                            <label class="custom-file-label" for="img_field">Selecciona una imagen</label>
                        </div>
                    </div>
                    @if ($errors->first('img'))
                        <small class="text-danger d-block">{{ $errors->first('img') }}</small>
                    @endif
                    <small>min: 48x48 max: 256x256 ratio: 1/1</small>
                    <div class="preview d-none mt-2 border p-3">
                        <figure class="m-0">
                            <img id="img_preview" src="{{ asset('img/no-photo.png') }}" alt="img" width="96">
                        </figure>
                    </div>
                </div>

                <input type="text" class="form-control d-none" id="img_link" name="img_link" placeholder="Url de la imagen" autofocus value="{{ old('img') }}">

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="url_img" name="url_img">
                    <label class="custom-control-label is-valid" for="url_img">
                        <small>Url de imagen</small>
                    </label>
                </div>
            </div>

        </div>

        @if ($season->use_rosters)
            <div class="form-group row pt-2">
                <label for="name" class="col-sm-3 col-form-label">Estadísticas</label>
                <div class="col-sm-9">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_mvp" name="stats_mvp">
                        <label class="custom-control-label" for="stats_mvp">MVP</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_goals" name="stats_goals">
                        <label class="custom-control-label" for="stats_goals">Goleadores</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_assists" name="stats_assists">
                        <label class="custom-control-label" for="stats_assists">Asistencias</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_yellow_cards" name="stats_yellow_cards">
                        <label class="custom-control-label" for="stats_yellow_cards">Tarjetas Amarillas</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_red_cards" name="stats_red_cards">
                        <label class="custom-control-label" for="stats_red_cards">Tarjetas Rojas</label>
                    </div>
                </div>
            </div>
        @endif
    </div>


    <div class="table-form-footer col-12 col-lg-8 col-xl-6 pt-3 px-3 px-md-0">
        <input type="submit" class="btn btn-primary border border-primary" value="Guardar" id="btnSave">
        <div class="no-close custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="no_close" name="no_close">
            <label class="custom-control-label is-valid" for="no_close">Insertar nuevo registro</label>
        </div>
    </div>

</form>