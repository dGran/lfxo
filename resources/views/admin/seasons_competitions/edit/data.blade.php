<form
    id="frmEdit"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.season_competitions.update', $competition->id) }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ method_field('PUT') }}
    {{ csrf_field() }}

    <div class="table-form-content col-12 col-lg-8 col-xl-6 p-md-3 animated fadeIn">
        <div class="form-group row pt-2">
            <label for="name" class="col-sm-3 col-form-label">Nombre</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" value="{{ old('name', $competition->name) }}" autofocus>
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
                            <button class="btn btn-danger {{ $competition->img ? 'd-inline-block' : 'd-none' }}" type="button" id="img_remove">Eliminar</button>
                        </div>
                         <div class="custom-file">
                            <input type="hidden" name="old_img" id="old_img" value="{{ $competition->img }}">
                            <input readonly type="file" class="custom-file-input" id="img_field" name="img">
                            <label class="custom-file-label" for="img_field">Selecciona una imagen</label>
                        </div>
                    </div>
                    @if ($errors->first('img'))
                        <small class="text-danger d-block">{{ $errors->first('img') }}</small>
                    @endif
                    <small>min: 48x48 max: 256x256 ratio: 1/1</small>
                    <div class="preview mt-2 border p-3 {{ $competition->img ? 'd-block' : 'd-none' }}">
                        <figure class="m-0">
                            <img id="img_preview" src="{{ $competition->getImgFormatted() }}" alt="img" width="96">
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

        @if ($competition->season->use_rosters)
            <div class="form-group row pt-2">
                <label for="name" class="col-sm-3 col-form-label">Estadísticas</label>
                <div class="col-sm-9">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_mvp" name="stats_mvp" {{ $competition->stats_mvp ? 'checked' : '' }}>
                        <label class="custom-control-label" for="stats_mvp">MVP</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_goals" name="stats_goals" {{ $competition->stats_goals ? 'checked' : '' }}>
                        <label class="custom-control-label" for="stats_goals">Goleadores</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_assists" name="stats_assists" {{ $competition->stats_assists ? 'checked' : '' }}>
                        <label class="custom-control-label" for="stats_assists">Asistencias</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_yellow_cards" name="stats_yellow_cards" {{ $competition->stats_yellow_cards ? 'checked' : '' }}>
                        <label class="custom-control-label" for="stats_yellow_cards">Tarjetas Amarillas</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="stats_red_cards" name="stats_red_cards" {{ $competition->stats_red_cards ? 'checked' : '' }}>
                        <label class="custom-control-label" for="stats_red_cards">Tarjetas Rojas</label>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <div class="table-form-footer col-12 col-lg-8 col-xl-6 pt-3 px-3 px-md-0">
        <input type="submit" class="btn btn-primary border border-primary" value="Guardar" id="btnSave">
    </div>

</form>