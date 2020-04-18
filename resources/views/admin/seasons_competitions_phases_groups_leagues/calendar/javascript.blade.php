<script>
    var competition_slug = {!! json_encode($group->phase->competition->slug) !!};
    var phase_slug = {!! json_encode($group->phase->slug) !!};
    var group_slug = {!! json_encode($group->slug) !!};
    var goals = {!! json_encode($group->phase->competition->stats_goals) !!};
    var assists = {!! json_encode($group->phase->competition->stats_assists) !!};

    $(function() {
    	$("#second_round").click(function(){
    		// $("#inverse_order").attr('disabled', !$("#inverse_order").attr('disabled'));
	    	if ($("#second_round").prop('checked')) {
	    		$("#inverse_order").attr('disabled', false);
	    		$("#lbinverse_order").removeClass('text-muted');
	    	} else {
	    		$("#inverse_order").attr('disabled', true);
	    		$("#inverse_order").prop('checked', false);
	    		$("#lbinverse_order").addClass('text-muted');
	    	}
    	});

        $('#updateModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");
            $.ajax({
                url: 'calendario/partido/' + id,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-update').html(data);
                $('#stats_mvp').selectpicker('refresh');
            });
        });

        $("#updateModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-update').html("");
        });

        $('#updateStatsModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");
            var url = '{{ route("admin.season_competitions_phases_groups_leagues.edit_match_stats", ":match_id") }}';
            url = url.replace(':match_id', id);
            $.ajax({
                url         : url,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-update-stats').html(data);
                $('#stats_mvp').selectpicker('refresh');
            });
        });

        $("#updateStatsModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-update-stats').html("");
        });

        $('#dayLimitModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");
            var url = '{{ route("admin.season_competitions_phases_groups_leagues.calendar.day.edit_limit", ":day_id") }}';
            url = url.replace(':day_id', id);
            $.ajax({
                url         : url,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-day-limit').html(data);
            });
        });

        $("#dayLimitModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-day-limit').html("");
        });

        $('#matchLimitModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");
            var url = '{{ route("admin.season_competitions_phases_groups_leagues.calendar.match.edit_limit", ":match_id") }}';
            url = url.replace(':match_id', id);
            $.ajax({
                url         : url,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-match-limit').html(data);
            });
        });

        $("#matchLimitModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-match-limit').html("");
        });


        $("#btnGenerate").click(function(){
            window.event.preventDefault();
            swal({
                title: "¿Estás seguro?",
                text: 'Al generar el calendario se resetearán todos los resultados registrados',
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
                    swal({
                        text: "Generando calendario, por favor espera...",
                        button: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });
                    $("#frmGenerate").submit();
                }
            });

        });

        $(".btnReset").click(function(e){
	        window.event.preventDefault();
	        var row = $(this).parents('tr');
	        var id = row.attr("data-id");
	        var name = row.attr("data-name");

	        swal({
	            title: "¿Estás seguro?",
	            text: 'Se va a resetear el resultado, estadísticas y economía del partido "' + name + '". No se podrán deshacer los cambios!',
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
	                var url = $(this).attr('href');
	            	$(location).attr('href', url);
	            }
	        });
        });

    });

    function sanction_local(id) {
    	if ($('#chk_local_sanctioned').prop('checked')) {
    		$("#local_score").val('0');
    		$('#local_score').prop('readonly', true);
    		$("#visitor_score").val('3');
    		$('#visitor_score').prop('readonly', true);
			$("#sanctioned_id").val(id);
	    	$('#chk_visitor_sanctioned').prop('disabled', true);
	    	$('#lb_visitor_sanctioned').addClass('text-muted');
    	} else {
    		$("#local_score").val('0');
    		$('#local_score').prop('readonly', false);
    		$("#visitor_score").val('0');
    		$('#visitor_score').prop('readonly', false);
			$("#sanctioned_id").val(null);
	    	$('#chk_visitor_sanctioned').prop('disabled', false);
	    	$('#lb_visitor_sanctioned').removeClass('text-muted');
    	}
    }

    function sanction_visitor(id) {
    	if ($('#chk_visitor_sanctioned').prop('checked')) {
    		$("#local_score").val('3');
    		$('#local_score').prop('readonly', true);
    		$("#visitor_score").val('0');
    		$('#visitor_score').prop('readonly', true);
			$("#sanctioned_id").val(id);
	    	$('#chk_local_sanctioned').prop('disabled', true);
	    	$('#lb_local_sanctioned').addClass('text-muted');
    	} else {
    		$("#local_score").val('0');
    		$('#local_score').prop('readonly', false);
    		$("#visitor_score").val('0');
    		$('#visitor_score').prop('readonly', false);
			$("#sanctioned_id").val(null);
	    	$('#chk_local_sanctioned').prop('disabled', false);
	    	$('#lb_local_sanctioned').removeClass('text-muted');
    	}
    }

    function sanctioned(local, id) {
    	window.event.preventDefault();
    	if (local) {
    		$("#local_score").val('0');
    		$("#visitor_score").val('3');
    	} else {
    		$("#local_score").val('3');
    		$("#visitor_score").val('0');
    	}
		$("#sanctioned_id").val(id);
    }

    function updateMatch() {
        window.event.preventDefault();
        var validate = true;
        var local_score = parseInt($("#local_score").val());
        var visitor_score = parseInt($("#visitor_score").val());
        if ($("#sanctioned_id").val() > 0) {
            match_santioned = true;
        } else {
            match_santioned = false;
        }

        if (!match_santioned) {
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
                    $("#btn_updateMatch").prop('disabled', true);
                    $("#frmUpdateMatch").submit();
                }
            });
        }
    }

</script>