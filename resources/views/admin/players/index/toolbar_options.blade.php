<div class="general-options">

    <div class="btn-toolbar px-3 pb-3 d-block d-md-none" role="toolbar">
        <div class="btn-group tableOptions" role="group">
            <button id="addon-new" onclick="location.href='{{ route('admin.players.add') }}'" type="button" class="btn btn-primary" data-toggle="button">
                <i class="fas fa-plus mr-2"></i>Nuevo
            </button>
            <button id="addon-filter" type="button" class="filter btn input-group-text border-left-0 {{ $filterName || $filterPlayerDb || $filterTeam || $filterNation || $filterPosition ? 'active' : '' }}" data-toggle="modal" data-target="#filterModal">
                <i class="fas fa-filter"></i>
            </button>
            @if ($players->count()>0)
                <div class="dropdown">
                    <button id="addon-images" type="button" class="btn input-group-text dropdown-toggle d-inline-block border-left-0 border-right-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 0">
                        <i class="fas fa-images"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right my-1" aria-labelledby="addon-images">
                        <a class="dropdown-item text-secondary link_web_images" href="{{ route('admin.players.link_web_images') }}">
                            <span class="fas fa-images fa-fw mr-1"></span>
                            <span>Enlazar imágenes (fifaindex)</span>
                        </a>
                        <a class="dropdown-item text-secondary unlink_web_images" href="{{ route('admin.players.unlink_web_images') }}">
                            <span class="fas fa-eraser fa-fw mr-1"></span>
                            <span>Eliminar imágenes enlazadas</span>
                        </a>
                    </div>
                </div>
            @endif
            <button id="addon-import" type="button" class="btn input-group-text" data-toggle="button" onclick="location.href='{{ route('admin.players.import.data') }}'">
                <i class="fas fa-file-import"></i>
            </button>
            @if ($players->count()>0)
                <div class="dropdown">
                    <button id="addon-export" type="button" class="btn input-group-text dropdown-toggle d-inline-block border-left-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 0px 4px 4px 0px">
                        <i class="fas fa-file-export"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right my-1" aria-labelledby="addon-export">
                        <a class="dropdown-item text-secondary" href="" onclick="export_file('xls')">
                            <span class="fas fa-file-export fa-fw mr-1"></span>
                            <span>Exportar (.xls)</span>
                        </a>
                        <a class="dropdown-item text-secondary" href="" onclick="export_file('xlsx')">
                            <span class="fas fa-file-export fa-fw mr-1"></span>
                            <span>Exportar (.xlsx)</span>
                        </a>
                        <a class="dropdown-item text-secondary" href="" onclick="export_file('csv')">
                            <span class="fas fa-file-export fa-fw mr-1"></span>
                            <span>Exportar (.csv)</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="btn-group d-none rowOptions" role="group">
            <button type="button" class="btn btn-danger" data-toggle="button" onclick="destroyMany()">
                <i class="fas fa-trash mr-2"></i>Eliminar
            </button>
            <button type="button" class="rowOptions-View btn btn-outline-secondary input-group-text border-left-0" data-toggle="button" onclick="view(this)">
                <i class="fas fa-id-card"></i>
            </button>
            <button type="button" class="rowOptions-Edit btn btn-outline-secondary input-group-text" data-toggle="button" onclick="edit(this)">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary input-group-text" data-toggle="button" onclick="duplicateMany()">
                <i class="fas fa-clone"></i>
            </button>
            <div class="dropdown">
                <button id="addon-link-images" type="button" class="btn input-group-text dropdown-toggle d-inline-block border-left-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 0">
                    <i class="fas fa-images"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right my-1" aria-labelledby="addon-images">
                    <a class="dropdown-item text-secondary link_web_images" href="" onclick="linkImageMany('fifaindex')">
                        <span class="fas fa-images fa-fw mr-1"></span>
                        <span>Enlazar imágen (pesdb)</span>
                    </a>
                    <a class="dropdown-item text-secondary link_web_images" href="" onclick="linkImageMany('sofifa')">
                        <span class="fas fa-images fa-fw mr-1"></span>
                        <span>Enlazar imágen (pesmaster)</span>
                    </a>
                    <a class="dropdown-item text-secondary unlink_web_images" href="" onclick="unlinkImageMany()">
                        <span class="fas fa-eraser fa-fw mr-1"></span>
                        <span>Eliminar imágen enlazada</span>
                    </a>
                </div>
            </div>
            <button id="row-addon-export" type="button" class="btn btn-outline-secondary input-group-text dropdown-toggle border-left-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 0px 4px 4px 0px">
                <i class="fas fa-file-export"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-right my-1" aria-labelledby="row-addon-export">
                <a class="dropdown-item text-secondary" href="" onclick="export_file_selected('xls')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.xls)</span>
                </a>
                <a class="dropdown-item text-secondary" href="" onclick="export_file_selected('xlsx')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.xlsx)</span>
                </a>
                <a class="dropdown-item text-secondary" href="" onclick="export_file_selected('csv')">
                    <span class="fas fa-file-export fa-fw mr-1"></span>
                    <span>Exportar (.csv)</span>
                </a>
            </div>
        </div>

    </div> {{-- toolbar --}}

</div> {{-- general-options --}}