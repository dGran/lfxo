<div class="tableOptions animated fadeIn">
    <a href="{{ route('admin.players.add') }}" class="btn btn-primary" id="btnAdd">
        <i class="fas fa-plus mr-2"></i><span>Nuevo</span>
    </a>
    <ul class="list-group border-top mt-3">
        @if ($players->count()>0)
            <li class="list-group-item border-0 px-0">
                <a href="{{ route('admin.players.link_web_images', 'fifaindex') }}" class="link_web_images">
                    <span class="fas fa-images fa-fw mr-1"></span>
                    <span>Enlazar imágenes (fifaindex)</span>
                </a>
            </li>
            <li class="list-group-item border-0 px-0">
                <a href="{{ route('admin.players.unlink_web_images') }}" class="unlink_web_images">
                    <span class="fas fa-eraser fa-fw mr-1"></span>
                    <span>Eliminar imágenes enlazadas</span>
                </a>
            </li>
        @endif
        <li class="list-group-item border-0 px-0">
            <a href="{{ route('admin.players.import.data') }}">
                <span class="fas fa-file-import fa-fw mr-1"></span>
                <span>Importar</span>
            </a>
        </li>
        @if ($players->count()>0)
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
        <li class="rowOptions-View list-group-item border-0 px-0 d-none">
            <a href="" onclick="view(this)">
                <span class="fas fa-id-card fa-fw mr-1"></span>
                <span>Info</span>
            </a>
        </li>
        <li class="rowOptions-Edit list-group-item border-0 px-0 d-none">
            <a href="" onclick="edit(this)">
                <span class="fas fa-edit fa-fw mr-1"></span>
                <span>Editar</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="duplicateMany()">
                <span class="fas fa-clone fa-fw mr-1"></span>
                <span>Duplicar</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="linkImageMany('pesdb')">
                <span class="fas fa-images fa-fw mr-1"></span>
                <span>Enlazar imágen (pesdb)</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="linkImageMany('pesmaster')">
                <span class="fas fa-images fa-fw mr-1"></span>
                <span>Enlazar imágen (pesmaster)</span>
            </a>
        </li>
        <li class="list-group-item border-0 px-0">
            <a href="" onclick="unlinkImageMany()">
                <span class="fas fa-eraser fa-fw mr-1"></span>
                <span>Eliminar imágen enlazada</span>
            </a>
        </li>
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

<form class="frmFilter" role="search" method="get" action="{{ route('admin.players') }}">
<input type="hidden" name="filtering" value="true"> {{-- field for controller --}}

{{-- search --}}
<div class="form-group row my-3">
    <div class="col">
    <div class="searchbox">
        <label class="search-icon" for="search-by"><i class="fas fa-search"></i></label>
        <input class="search-input form-control mousetrap filterName" name="filterName" type="text" placeholder="Buscar..." value="{{ $filterName ? $filterName : '' }}" autocomplete="off" onkeypress="submitFilterForm()">
        <span class="search-clear"><i class="fas fa-times"></i></span>
        </div>
    </div>
</div>

<div class="mt-4">
    @if ($filterName || $filterPlayerDb || $filterTeam || $filterNation || $filterPosition)
        <ul class="nav mb-2">
            @if ($filterName)
                <li class="nav-item">
                    <a href="" class="badge badge-secondary mr-1" onclick="cancelFilterName()">
                        <span class="r-1">Nombre</span>
                        <i class="fas fa-times"></i>
                    </a>
                </li>
            @endif
            @if ($filterPlayerDb)
                <li class="nav-item">
                    <a href="" class="badge badge-secondary mr-1" onclick="cancelFilterPlayerDb()">
                        <span class="r-1">Database</span>
                        <i class="fas fa-times"></i>
                    </a>
                </li>
            @endif
            @if ($filterTeam)
                <li class="nav-item">
                    <a href="" class="badge badge-secondary mr-1" onclick="cancelFilterTeam()">
                        <span class="r-1">Equipo</span>
                        <i class="fas fa-times"></i>
                    </a>
                </li>
            @endif
            @if ($filterNation)
                <li class="nav-item">
                    <a href="" class="badge badge-secondary mr-1" onclick="cancelFilterNation()">
                        <span class="r-1">País</span>
                        <i class="fas fa-times"></i>
                    </a>
                </li>
            @endif
            @if ($filterPosition)
                <li class="nav-item">
                    <a href="" class="badge badge-secondary mr-1" onclick="cancelFilterPosition()">
                        <span class="r-1">Posición</span>
                        <i class="fas fa-times"></i>
                    </a>
                </li>
            @endif
        </ul>
    @endif
    <h4 class="p-2 bg-light">Filtros</h4>
    <div class="form-group row">
        <div class="col-sm-12">
            <label for="filterCategoryLarge" class="mb-1">Player Databases</label>
            <select name="filterPlayerDb" id="filterPlayerDbLarge" class="selectpicker form-control filterPlayerDb" onchange="applyfilterPlayerDb()">
                <option value="">Todas las player databases</option>
                @foreach ($players_dbs as $players_db)
                    @if ($players_db->id == $filterPlayerDb)
                        <option selected value="{{ $players_db->id }}">{{ $players_db->name }}</option>
                    @else
                        <option value="{{ $players_db->id }}">{{ $players_db->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <label for="filterTeam" class="mb-1">Equipo</label>
            <input class="filterTeam-input form-control mousetrap filterTeam" id="filterTeam" name="filterTeam" type="text" placeholder="Equipo..." value="{{ $filterTeam ? $filterTeam : '' }}" autocomplete="off" onkeypress="submitFilterForm()">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <label for="filterNation" class="mb-1">País</label>
            <input class="filterNation-input form-control mousetrap filterNation" id="filterNation" name="filterNation" type="text" placeholder="País..." value="{{ $filterNation ? $filterNation : '' }}" autocomplete="off" onkeypress="submitFilterForm()">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <label for="filterPosition" class="mb-1">Posición</label>
            <input class="filterPosition-input form-control mousetrap filterPosition" id="filterPosition" name="filterPosition" type="text" placeholder="Posición..." value="{{ $filterPosition ? $filterPosition : '' }}" autocomplete="off" onkeypress="submitFilterForm()">
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
                <option value="name" {{ $order == 'name' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-up">Por nombre</option>
                <option value="name_desc" {{ $order == 'name_desc' ? 'selected' : '' }} data-icon="fas fa-sort-alpha-down">Por nombre</option>
                <option value="overall" {{ $order == 'overall' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-up">Por media</option>
                <option value="overall_desc" {{ $order == 'overall_desc' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-down">Por media</option>
                <option value="age" {{ $order == 'age' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-up">Por edad</option>
                <option value="age_desc" {{ $order == 'age_desc' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-down">Por edad</option>
                <option value="height" {{ $order == 'height' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-up">Por altura</option>
                <option value="height_desc" {{ $order == 'height_desc' ? 'selected' : '' }} data-icon="fas fa-sort-numeric-down">Por altura</option>
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