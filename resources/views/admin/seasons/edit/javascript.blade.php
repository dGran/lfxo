<script>
    var database_id_on_enter = {!! json_encode($season->players_db_id) !!};
    var user_rosters_on_enter = {!! json_encode($season->use_rosters) !!};

    $(function() {
        Mousetrap.bind(['command+s', 'ctrl+s'], function() {
            $("#frmEdit").submit();
            return false;
        });
        Mousetrap.bind(['command+c', 'ctrl+c'], function() {
            var url = $("#btnCancel").attr('href');
            $(location).attr('href', url);
            return false;
        });

        $("#frmEdit").submit(function(event) {
            $("#btnSave").attr("disabled", "disabled");

            if ($("#use_rosters").prop('checked')) {
                if (database_id_on_enter != $("#players_db_id").val()) {
                    if ($("#players_db_id").val() > 0) {
                        $("#loading").removeClass('d-none');
                        $("#btnSave").val('Eliminando / importando jugadores, por favor espere..');
                    } else {
                        $("#loading").removeClass('d-none');
                        $("#btnSave").val('Eliminando jugadores, por favor espere..');
                    }
                }
            } else {
                if (user_rosters_on_enter) {
                    $("#btnSave").val('Eliminando jugadores, por favor espere..');
                    $("#loading").removeClass('d-none');
                }
            }

        });

        $("#use_economy").click(function(){
            if( $('#use_economy').prop('checked') ) {
                $(".initial_budget").removeClass('d-none');
                $(".initial_budget").addClass('d-block');
                $(".salaries_paid").removeClass('d-none');
                $(".salaries_paid").addClass('d-block');
            } else {
                $(".initial_budget").addClass('d-none');
                $(".initial_budget").removeClass('d-block');
                $(".salaries_paid").addClass('d-none');
                $(".salaries_paid").removeClass('d-block');
            }
        });

        $("#use_rosters").click(function(){
            if( $('#use_rosters').prop('checked') ) {
                $(".roster_options").removeClass('d-none');
                $(".roster_options").addClass('d-block');
            } else {
                $(".roster_options").addClass('d-none');
                $(".roster_options").removeClass('d-block');
            }
        });

        $('#rules').summernote({
            height: '150px',
            // placeholder: 'Escribe el contenido de la publicación',
            dialogsFade: true,
            fontNames: ['Arial', 'Arial Black'],
            lang: 'es-ES',
            height: 200,
            toolbar: [
                [['style', 'bold', 'italic', 'underline']],
                ['fontsize', ['color', 'fontname', 'fontsize', 'bold', 'italic', 'underline']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['hr']],
                ['view', ['codeview', 'fullscreen']],
            ]
        });
    });

</script>