<script>
    var competition_slug = {!! json_encode($competition->slug) !!};
    var season_slug = {!! json_encode(active_season()->slug) !!};

    $(function() {
        $('#updateModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");

            var url = '{{ route("competitions.calendar.match.edit", [":season_slug", ":competition_slug", ":match_id"]) }}';
            url = url.replace(':season_slug', season_slug);
            url = url.replace(':competition_slug', competition_slug);
            url = url.replace(':match_id', id);

            $.ajax({
                url         : url,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-update').html(data);
                // $('#stats_mvp').selectpicker('refresh');
            });
        });

        $("#updateModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-update').html("");
        });
    });

    function enable_disable_penalties(e) {
        var row = $(e).parents('tr');
        var round_trip = row.attr("data-round-round_trip");
        var double_value = row.attr("data-round-double_value");
        var prev_local_score = row.attr("data-prev-match-local_score");
        var prev_visitor_score = row.attr("data-prev-match-visitor_score");
        var match_order = row.attr("data-match_order");
        var local_score = parseInt($("#local_score").val());
        var visitor_score = parseInt($("#visitor_score").val());

        if (round_trip == 1) {
            if (match_order == 2) {
                var local_score_total = parseInt($("#local_score").val()) + parseInt(prev_visitor_score);
                var visitor_score_total = parseInt($("#visitor_score").val()) + parseInt(prev_local_score);

                if (double_value == 1) {
                    if ( (local_score == prev_local_score) && (visitor_score == prev_visitor_score) ) {
                        $("#penalties_local_score").prop('disabled', false);
                        $("#penalties_visitor_score").prop('disabled', false);

                        $("#btn_updateMatch").addClass('disabled');
                    } else {
                        $("#penalties_local_score").prop('disabled', true);
                        $("#penalties_local_score").val('0');
                        $("#penalties_visitor_score").prop('disabled', true);
                        $("#penalties_visitor_score").val('0');

                        $("#btn_updateMatch").removeClass('disabled');
                    }
                } else {
                    if (local_score_total == visitor_score_total) {
                        $("#penalties_local_score").prop('disabled', false);
                        $("#penalties_visitor_score").prop('disabled', false);

                        $("#btn_updateMatch").addClass('disabled');
                    } else {
                        $("#penalties_local_score").prop('disabled', true);
                        $("#penalties_local_score").val('0');
                        $("#penalties_visitor_score").prop('disabled', true);
                        $("#penalties_visitor_score").val('0');

                        $("#btn_updateMatch").removeClass('disabled');
                    }
                }
            } else {
                $("#btn_updateMatch").removeClass('disabled');
            }
        } else {
            if (local_score == visitor_score) {
                $("#penalties_local_score").prop('disabled', false);
                $("#penalties_visitor_score").prop('disabled', false);

                $("#btn_updateMatch").addClass('disabled');
            } else {
                $("#penalties_local_score").prop('disabled', true);
                $("#penalties_local_score").val('0');
                $("#penalties_visitor_score").prop('disabled', true);
                $("#penalties_visitor_score").val('0');

                $("#btn_updateMatch").removeClass('disabled');
            }
        }
    }

    function validate_penalties(e) {
        if ($("#penalties_local_score").val() == $("#penalties_visitor_score").val()) {
            $("#btn_updateMatch").addClass('disabled');
        } else {
            $("#btn_updateMatch").removeClass('disabled');
        }
    }

    function updateMatch(goals, assists) {
        window.event.preventDefault();
        var validate = true;
        var local_score = parseInt($("#local_score").val());
        var visitor_score = parseInt($("#visitor_score").val());

        if (goals) {
            var local_goals = 0;
            $(".local_goals").each(function(){
                if (!$(this).val()) {
                    partial = 0;
                } else {
                    partial = parseInt($(this).val());
                    local_goals = local_goals + partial;
                }
            });
            var visitor_goals = 0;
            $(".visitor_goals").each(function(){
                if (!$(this).val()) {
                    partial = 0;
                } else {
                    partial = parseInt($(this).val());
                    visitor_goals = visitor_goals + partial;
                }
            });
            if (local_score != local_goals || visitor_score != visitor_goals) {
                validate = false;
                swal({
                    className: "swal-error",
                    text : 'Los goleadores anotados no coinciden con el resultado de cada equipo',
                    timer: 3000,
                    button: false,
                });
                return false;
            }
        }

        if (assists) {
            var local_assists = 0;
            $(".local_assists").each(function(){
                if (!$(this).val()) {
                    partial = 0;
                } else {
                    partial = parseInt($(this).val());
                    local_assists = local_assists + partial;
                }
            });
            var visitor_assists = 0;
            $(".visitor_assists").each(function(){
                if (!$(this).val()) {
                    partial = 0;
                } else {
                    partial = parseInt($(this).val());
                    visitor_assists = visitor_assists + partial;
                }
            });
            if (local_score != local_assists || visitor_score != visitor_assists) {
                validate = false;
                swal({
                    className: "swal-error",
                    text : 'Las asistencias anotadas no coinciden con el resultado de cada equipo',
                    timer: 3000,
                    button: false,
                });
                return false;
            }
        }

        if (validate) {
            swal({
                title: 'Confirmación de datos',
                text: 'Comprueba el resultado, goleadores, tarjetas...antes de enviar el resultado, ¿está todo correcto?. ',
                buttons: {
                    confirm: {
                        text: "Sí, enviar resultado",
                        value: true,
                        visible: true,
                        className: "btn btn-primary btn-sm",
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
                    $("#btn_updateMatch").addClass('disabled');
                    $("#frmUpdateMatch").submit();
                }
            });
        }
    }
</script>