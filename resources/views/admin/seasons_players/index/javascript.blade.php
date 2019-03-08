<script>
    var filterName = {!! json_encode($filterName) !!};
    var filterSeason = {!! json_encode($filterSeason) !!};
    var filterParticipant = {!! json_encode($filterParticipant) !!};
    var filterTeam = {!! json_encode($filterTeam) !!};
    var filterNation = {!! json_encode($filterNation) !!};
    var filterPosition = {!! json_encode($filterPosition) !!};
    var order = {!! json_encode($order) !!};
    var pagination = {!! json_encode($pagination) !!};

    $(function() {
        Mousetrap.bind(['command+a', 'ctrl+a'], function() {
            var url = $("#btnAdd").attr('href');
            $(location).attr('href', url);
            return false;
        });
        Mousetrap.bind(['command+f', 'ctrl+f'], function() {
            $('.search-input').focus();
            return false;
        });

        $('.search-clear').on("click", function() {
            $('.search-input').val('');
            $('.frmFilter').submit();
        });
        $('.search-input').on("blur", function() {
            $(this).val(filterName);
        });
        $('.filterTeam-input').on("blur", function() {
            $(this).val(filterTeam);
        });
        $('.filterNation-input').on("blur", function() {
            $(this).val(filterNation);
        });
        $('.filterPosition-input').on("blur", function() {
            $(this).val(filterPosition);
        });

        $('#viewModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-player-id");
            $.ajax({
                url: 'jugadores/ver/' + id,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('.modal-dialog').html(data);
            });
        });

        $("#viewModal").on("hidden.bs.modal", function(){
            $('.modal-dialog').html("");
        });

    });

    function applyDisplay() {
        $('.frmFilter').submit();
    }

    function applyOrder() {
        $('.frmFilter').submit();
    }

    function applyfilterSeason() {
        $('.frmFilter').submit();
    }

    function applyfilterParticipant() {
        $('.frmFilter').submit();
    }

    function cancelFilterName() {
        window.event.preventDefault();
        $('.filterName').val('');
        if (filterParticipant || filterTeam || filterNation || filterPosition || order || pagination) {
            $('.frmFilter').submit();
        } else {
            cancelFilters();
        }
    }

    function cancelFilterParticipant() {
        window.event.preventDefault();
        $('.filterParticipant').val('');
        if (filterName || filterTeam || filterNation || filterPosition || order || pagination) {
            $('.frmFilter').submit();
        } else {
            cancelFilters();
        }
    }

    function cancelFilterTeam() {
        window.event.preventDefault();
        $('.filterTeam').val('');
        if (filterName || filterParticipant || filterNation || filterPosition || order || pagination) {
            $('.frmFilter').submit();
        } else {
            cancelFilters();
        }
    }

    function cancelFilterNation() {
        window.event.preventDefault();
        $('.filterNation').val('');
        if (filterName || filterParticipant || filterTeam || filterPosition || order || pagination) {
            $('.frmFilter').submit();
        } else {
            cancelFilters();
        }
    }

    function cancelFilterPosition() {
        window.event.preventDefault();
        $('.filterPosition').val('');
        if (filterName || filterParticipant || filterTeam || filterNation || order || pagination) {
            $('.frmFilter').submit();
        } else {
            cancelFilters();
        }
    }

    function cancelFilters() {
        window.location.href = '{{ route("admin.season_players") }}';
    }

    function submitFilterForm() {
        $('input').keypress(function (e) {
            if (e.which == 13) {
                $('.frmFilter').submit();
            }
        });
    }

    $(".btn-delete").click(function(e) {
        window.event.preventDefault();
        var row = $(this).parents('tr');
        var id = row.attr("data-id");
        var name = row.attr("data-name");
        var allow_delete = row.attr("data-allow-delete");

        if (allow_delete == 1) {
            swal({
                title: "¿Estás seguro?",
                text: 'Se va a eliminar el jugador "' + name + '". No se podrán deshacer los cambios!',
                buttons: {
                    confirm: {
                        text: "Sí, estoy seguro",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    },
                    cancel: {
                        text: "No, cancelar",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true,
                    }
                },
                closeOnClickOutside: false,
            })
            .then((value) => {
                if (value) {
                    var form = $('#form-delete');
                    var url = form.attr('action').replace(':PLAYER_ID', id);
                    form.attr('action', url);
                    form.submit();
                }
            });
        } else {
            swal("El jugador pertenece a un participante por lo que no se puede eliminar.", {
                buttons: false,
                icon: "error",
                timer: 3000,
            });
        }

    });

    function destroyMany() {
        window.event.preventDefault();
        disabledActionsButtons();
        swal({
            title: "¿Estás seguro?",
            text: 'Se van a eliminar los jugadores seleccionados (los jugadores perteneciantes de participantes no se eliminarán). No se podrán deshacer los cambios!.',
            buttons: {
                confirm: {
                    text: "Sí, estoy seguro",
                    value: true,
                    visible: true,
                    className: "btn btn-danger",
                    closeModal: true
                },
                cancel: {
                    text: "No, cancelar",
                    value: null,
                    visible: true,
                    className: "btn btn-secondary",
                    closeModal: true,
                }
            },
            closeOnClickOutside: false,
        })
        .then((value) => {
            if (value) {
                var ids = [];
                $(".mark:checked").each(function() {
                    ids.push($(this).val());
                });
                var url = '{{ route("admin.season_players.destroy.many", ":ids") }}';
                url = url.replace(':ids', ids);
                window.location.href=url;
            } else {
                enabledActionsButtons();
            }
        });
    }

    function edit(element) {
        $(".mark:checked").each(function() {
            id = $(this).val();
        });
        url = $('#btnEdit'+id).attr("href");
        if ($(element).is('button')) {
            window.location.href=url;
        } else {
            $(element).attr("href", url);
        }
    }

    function view(element) {
        window.event.preventDefault();

        $(".mark:checked").each(function() {
            id = $(this).val();
        });
        url = $('#btnView'+id).attr("href");
        $('#btnView'+id).trigger('click');
    }

    function rowSelect(element) {
        $(element).siblings('.select').find('.mark').trigger('click');
    }

    function showHideRowOptions(element) {
        if ($(element).is(':checked')) {
            $(element).parents('tr').addClass('selected');
        } else {
            $(element).parents('tr').removeClass('selected');
        }

        if ($(".mark:checked").length > 0) {
            if (!$(".rowOptions").is(':visible')) {
                $(".rowOptions").removeClass('d-none');
                $(".tableOptions").addClass('d-none');
            }
            if ($(".mark:checked").length == 1) {
                $(".rowOptions-Edit").removeClass('d-none');
                $(".rowOptions-View").removeClass('d-none');
            } else {
                $(".rowOptions-Edit").addClass('d-none');
                $(".rowOptions-View").addClass('d-none');
            }
        } else {
            if ($(".rowOptions").is(':visible')) {
                $(".rowOptions").addClass('d-none');
                $(".tableOptions").removeClass('d-none');
            }
        }
    }

    function showHideAllRowOptions() {
        if ($("#allMark").is(':checked')) {
            $(".mark").prop('checked', true);
            $(".mark").parents('tr').addClass('selected');
        } else {
            $(".mark").prop('checked', false);
            $(".mark").parents('tr').removeClass('selected');
        }
        showHideRowOptions();
    }

    function disabledActionsButtons() {
        $('a').addClass('disabled');
        $('button').attr("disabled", "disabled");
    }

    function enabledActionsButtons() {
        $('a').removeClass('disabled');
        $('button').removeAttr("disabled");
    }

    function export_file(type) {
        window.event.preventDefault();

        swal({
            title: "Exportar todos los registros",
            text: 'Introduce nombre del archivo (opcional)',
            content: "input",
            buttons: {
                cancel: {
                    text: "Cancelar",
                    value: null,
                    visible: true,
                    className: "btn btn-secondary",
                    closeModal: true,
                },
                confirm: {
                    text: "Continuar",
                    value: true,
                    visible: true,
                    className: "btn btn-primary",
                    closeModal: true
                }
            },
        })
        .then((value) => {
            if (value) {
                var filename = `${value}`;
                if (!filename ) {
                    var time = Math.floor(new Date().getTime() / 1000);
                    var filename = 'participantes_export' + time;
                }
                $(location).attr('href', 'participantes/exportar/' + filename + '/' + type + '/' + filterSeason + '/' + order);
            }
        });
    }

    function export_file_selected(type) {
        window.event.preventDefault();

        var ids = [];
        $(".mark:checked").each(function() {
            ids.push($(this).val());
        });

        swal({
            title: "Exportar los registros seleccionados",
            text: 'Introduce nombre del archivo (opcional)',
            content: "input",
            buttons: {
                cancel: {
                    text: "Cancelar",
                    value: null,
                    visible: true,
                    className: "btn btn-secondary",
                    closeModal: true,
                },
                confirm: {
                    text: "Continuar",
                    value: true,
                    visible: true,
                    className: "btn btn-primary",
                    closeModal: true
                }
            },
        })
        .then((value) => {
            if (value) {
                var filename = `${value}`;
                if (!filename ) {
                    var time = Math.floor(new Date().getTime() / 1000);
                    var filename = 'participantes_export' + time;
                }
                $(location).attr('href', 'participantes/exportar/' + filename + '/' + type + '/' + filterName + '/' + order + '/' + ids);
            }
        });
    }

    function import_file() {
        window.event.preventDefault();
        swal({
            title: "Importar datos",
            text: 'Se van a importar los datos del archivo seleccionado, pulsa continuar y selecciona el archivo que contiene los datos (.xls, .xlsx, .csv).',
            buttons: {
                cancel: {
                    text: "Cancelar",
                    value: null,
                    visible: true,
                    className: "btn btn-secondary",
                    closeModal: true,
                },
                confirm: {
                    text: "Continuar",
                    value: true,
                    visible: true,
                    className: "btn btn-primary",
                    closeModal: true
                }
            },
            closeOnClickOutside: false,
        })
        .then((value) => {
            if (value) {
                $("#import_file").trigger('click');
            }
        });
    }

    $('#import_file').change(function(){
        $("#frmImport").submit();
    });

</script>