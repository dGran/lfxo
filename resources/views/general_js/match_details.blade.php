<script>
    var competition_slug = {!! json_encode($competition->slug) !!};
    var season_slug = {!! json_encode(active_season()->slug) !!};

    $(function() {
        $('#matchDetailsModal').on('show.bs.modal', function(e) {
            var row = $(e.relatedTarget).parents('tr');
            var id = row.attr("data-id");

            var url = '{{ route("competitions.calendar.match.details", [":season_slug", ":competition_slug", ":match_id"]) }}';
            url = url.replace(':season_slug', season_slug);
            url = url.replace(':competition_slug', competition_slug);
            url = url.replace(':match_id', id);

            $.ajax({
                url         : url,
                type        : 'GET',
                datatype    : 'html',
            }).done(function(data){
                $('#modal-dialog-match-details').html(data);
            });
        });

        $("#matchDetailsModal").on("hidden.bs.modal", function(){
            $('#modal-dialog-match-details').html("");
        });
    });

</script>