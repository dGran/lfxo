<div class="tableOptions animated fadeIn">
    <a href="{{ route('admin.season_participants.add', $active_season->id) }}" class="btn btn-primary" id="btnAdd">
        <i class="fas fa-plus mr-2"></i><span>Nuevo</span>
    </a>
    <ul class="list-group border-top mt-3">
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="import_file()">
               <form
                id="frmImport"
                    lang="{{ app()->getLocale() }}"
                    role="form"
                    method="POST"
                    action="{{ route('admin.season_participants.import.file') }}"
                    enctype="multipart/form-data"
                    data-toggle="validator"
                    autocomplete="off">
                    {{ csrf_field() }}
                    <input type="file" name="import_file" id="import_file" class="d-none">
                    <span class="fas fa-file-import fa-fw mr-1"></span>
                    <span>Importar</span>
                </form>
            </a>
        </li>
        @if ($participants->count()>0)
            <li class="list-group-item border-0 px-0">
                <a href="" onclick="export_file('xls')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.xls)</span>
                </a>
            </li>
            <li class="list-group-item border-0 px-0">
                <a href="" onclick="export_file('xlsx')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.xlsx)</span>
                </a>
            </li>
            <li class="list-group-item border-0 px-0">
                <a href="" onclick="export_file('csv')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.csv)</span>
                </a>
            </li>
        @endif
    </ul>
</div>

<div class="rowOptions animated fadeIn d-none">
    <a href="" class="btn btn-danger" onclick="destroyMany()">
        <i class="fas fa-trash mr-2"></i>Eliminar
    </a>
    <ul class="list-group border-top mt-3">
        <li class="rowOptions-Edit list-group-item border-0 px-0 d-none">
            <a href="" onclick="edit(this)">
                <span class="fas fa-edit fa-fw mr-1"></span>
                <span>Editar</span>
            </a>
        </li>
        @if ($active_season->use_economy)
            <li class="rowOptions-CashHistory list-group-item border-0 px-0 d-none">
                <a href="" onclick="cashHistory(this)">
                    <span class="fas fa-piggy-bank fa-fw mr-1"></span>
                    <span>Historial de economía</span>
                </a>
            </li>
        @endif
        @if ($active_season->use_rosters)
            <li class="rowOptions-Roster list-group-item border-0 px-0 d-none">
                <a href="" onclick="roster(this)">
                    <span class="fas fa-user-shield fa-fw mr-1"></span>
                    <span>Plantilla</span>
                </a>
            </li>
        @endif
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="export_file_selected('xls')">
                <span class="fas fa-file-export fa-fw mr-1"></span>
                <span>Exportar (.xls)</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="export_file_selected('xlsx')">
                <span class="fas fa-file-export fa-fw mr-1"></span>
                <span>Exportar (.xlsx)</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="export_file_selected('csv')">
                <span class="fas fa-file-export fa-fw mr-1"></span>
                <span>Exportar (.csv)</span>
            </a>
        </li>
    </ul>
</div>

<form class="frmFilter" role="search" method="get" action="{{ route('admin.season_participants') }}">
<input type="hidden" name="filtering" value="true"> {{-- field for controller --}}

<div class="mt-2">
    <h4 class="p-2 bg-light">Filtros</h4>
    <div class="form-group row">
        <div class="col-sm-12">
            <label for="filterSeasonLarge" class="mb-1">Temporadas</label>
            <select name="filterSeason" id="filterSeasonLarge" class="selectpicker form-control filterSeason" onchange="applyfilterSeason()">
                @foreach ($seasons as $season)
                    @if ($season->id == $filterSeason)
                        <option selected value="{{ $season->id }}">{{ $season->name }}</option>
                    @else
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

</div>

<div class="mt-4">
    <h4 class="p-2 bg-light">Orden</h4>
    <div class="form-group row">
        <div class="col-sm-12">
            <select name="order" class="selectpicker show-tick form-control order" onchange="applyOrder()">
                <option value="default" {{ $order == 'default' ? 'selected' : '' }}>Por defecto</option>
                <option value="date_desc" {{ $order == 'date_desc' ? 'selected' : '' }} data-icon="fas fa-sort-amount-up">Los últimos al principio</option>
                <option value="date" {{ $order == 'date' ? 'selected' : '' }} data-icon="fas fa-sort-amount-down">Los últimos al final</option>
                @if ($active_season->participant_has_team)
                    <option value="team" {{ $order == 'team' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-up">Por nombre de equipo</option>
                    <option value="team_desc" {{ $order == 'team_desc' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-down">Por nombre de equipo</option>
                @endif
                <option value="user" {{ $order == 'user' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-up">Por nombre de usuario</option>
                <option value="user_desc" {{ $order == 'user_desc' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-down">Por nombre de usuario</option>
            </select>
        </div>
    </div>
</div>

<div class="mt-4">
    <h4 class="p-2 bg-light">Visualización</h4>
    <div class="form-group row">
        <div class="col-sm-12">
            <select name="pagination" class="selectpicker show-tick form-control pagination" onchange="applyDisplay()">
                <option value="6" {{ $pagination == '6' ? 'selected' : '' }}>6 registros / pagina</option>
                <option value="12" {{ $pagination == '12' || !$pagination ? 'selected' : '' }}>12 registros / pagina</option>
                <option value="20" {{ $pagination == '20' ? 'selected' : '' }}>20 registros / pagina</option>
                <option value="50" {{ $pagination == '50' ? 'selected' : '' }}>50 registros / pagina</option>
                <option value="100" {{ $pagination == '100' ? 'selected' : '' }}>100 registros / pagina</option>
            </select>
        </div>
    </div>
</div>

</form>