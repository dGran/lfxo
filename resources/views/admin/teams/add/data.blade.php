<form
    id="frmAdd"
    lang="{{ app()->getLocale() }}"
    role="form"
    method="POST"
    action="{{ route('admin.teams.save') }}"
    enctype="multipart/form-data"
    data-toggle="validator"
    autocomplete="off">
    {{ csrf_field() }}

    <div class="table-form-content col-12 col-lg-8 col-xl-6 p-md-3 animated fadeIn">
        <div class="form-group row pt-2">
            <label for="name" class="col-sm-2 col-form-label">Nombre</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" autofocus value="{{ old('name') }}">
            </div>
        </div>
        <div class="form-group row">
            <label for="team_category_id" class="col-sm-2 col-form-label">Categoría</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" name="team_category_id" id="team_category_id" data-size="3">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="logo" class="col-sm-2 col-form-label">Escudo</label>

            <div class="col-sm-10">
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <button class="btn btn-danger d-none" type="button" id="logo_remove">Eliminar</button>
                        {{-- <span class="input-group-text">Eliminar</span> --}}
                    </div>
                     <div class="custom-file">
                        <input readonly type="file" class="custom-file-input" id="logo_field" name="logo">
                        <label class="custom-file-label" for="logo_field">Selecciona una imagen</label>
                    </div>
                </div>
                <small>min: 48x48 max: 256x256 ratio: 1/1</small>
                <div class="preview d-none mt-2 border p-3">
                    <figure class="m-0">
                        <img id="logo_preview" src="{{ asset('img/no-photo.png') }}" alt="logo" width="96">
                    </figure>
                </div>
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