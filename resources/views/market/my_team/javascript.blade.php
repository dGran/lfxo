<script>
    $('#viewModal').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).attr("data-id");
        $.ajax({
            url: 'mi-equipo/jugador/' + id,
            type        : 'GET',
            datatype    : 'html',
        }).done(function(data){
            $('#modal-dialog-view').html(data);
        });
    });

    $("#viewModal").on("hidden.bs.modal", function(){
        $('#modal-dialog-view').html("");
    });

    $('#editModal').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).attr("data-id");
        $.ajax({
            url: 'mi-equipo/jugador/editar/' + id,
            type        : 'GET',
            datatype    : 'html',
        }).done(function(data){
            $('#modal-dialog-edit').html(data);
        });
    });

    $("#editModal").on("hidden.bs.modal", function(){
        $('#modal-dialog-edit').html("");
    });

    function changeSalary() {
        $('#price').val($('#salary').val() * 10);
    }

    function salaryBlur() {
        var value = $('#salary').val();
        var min = $('#salary').attr('min');
        var max = $('#salary').attr('max');

        if (value < min) {
            $('#salary').val(min);
            $('#price').val($('#salary').val() * 10);
        }
        if (value > max) {
            $('#salary').val(max);
        }
        $('#price').val($('#salary').val() * 10);
    }

    function changePrice() {
        $('#salary').val($('#price').val() / 10);
    }

    function priceBlur() {
        var value = $('#price').val();
        var min = $('#salary').attr('min') * 10;
        var max = $('#salary').attr('max') * 10;

        if (value < min) {
            $('#price').val(min);
            $('#salary').val($('#price').val() / 10);
        }
        if (value > max) {
            $('#price').val(max);
        }
        $('#salary').val($('#price').val() / 10);
    }

    function transferableChange() {
        if ($('#transferable').prop('checked') == true) {
            $('#untransferable').prop('checked', false);

            $('#sale_price').prop('disabled', false);
            $('#sale_auto_accept').prop('disabled', false);
            $('#market_phrase').prop('disabled', false);
        } else {
            $('#sale_price').val('');
            $('#sale_price').prop('disabled', true);
            $('#sale_auto_accept').prop('checked', false);
            $('#sale_auto_accept').prop('disabled', true);
            if ($('#player_on_loan').prop('checked') == true) {
                $('#market_phrase').prop('disabled', false);
            } else {
                $('#market_phrase').val('');
                $('#market_phrase').prop('disabled', true);
            }
        }
    }

    function onLoanChange() {
        if ($('#player_on_loan').prop('checked') == true) {
            $('#untransferable').prop('checked', false);
            $('#market_phrase').prop('disabled', false);
        } else {
            if ($('#transferable').prop('checked') == true) {
                $('#market_phrase').prop('disabled', false);
            } else {
                $('#market_phrase').val('');
                $('#market_phrase').prop('disabled', true);
            }
        }
    }

    function untransferableChange() {
        if ($('#untransferable').prop('checked') == true) {
            $('#transferable').prop('checked', false);
            $('#sale_price').val('');
            $('#sale_price').prop('disabled', true);
            $('#sale_auto_accept').prop('checked', false);
            $('#sale_auto_accept').prop('disabled', true);
            $('#player_on_loan').prop('checked', false);
            $('#market_phrase').val('');
            $('#market_phrase').prop('disabled', true);
        }
    }

    function phraseCounterFocus() {
        $('#phrase_counter').removeClass('d-none');
    }

    function phraseCounter() {
        $('#phrase_counter').text($('#market_phrase').val().length + ' / 80');
    }

    function phraseCounterBlur() {
        $('#phrase_counter').addClass('d-none');
    }

    function dismiss_player(id, name, remuneration) {
        window.event.preventDefault();
        swal({
            title: 'Despedir a "' + name + '"',
            text: 'Recibirás ' + remuneration + ' millones.',
            buttons: {
                confirm: {
                    text: "Sí, estoy seguro",
                    value: true,
                    visible: true,
                    className: "btn btn-danger btn-sm",
                    closeModal: true
                },
                cancel: {
                    text: "No, cancelar",
                    value: null,
                    visible: true,
                    className: "btn btn-secondary btn-sm",
                    closeModal: true,
                }
            },
            closeOnClickOutside: false,
            closeOnEsc: false,
        })
        .then((value) => {
            if (value) {
                var url = '{{ route("market.my_team.player.dismiss", ":id") }}';
                url = url.replace(':id', id);
                window.location.href = url;
            }
        });
    }
</script>