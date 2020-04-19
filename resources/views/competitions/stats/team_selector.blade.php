<div class="team-selector">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12 col-md-10 col-lg-8 px-3 px-md-0 py-3">
				<select class="selectpicker show-tick btn-light form-control" id="participant_selector" data-size="8">
					<option {{ $participant_id == 0 ? 'selected' : '' }} value="{{ route('competitions.stats', [$competition->season->slug, $competition->slug]) }}">
						Todos los equipos
					</option>
					@foreach ($global_participants as $participant)
						<option {{ $participant->participant->id == $participant_id ? 'selected' : '' }} title="<img class='mr-2' src='{{ $participant->participant->logo() }}' width='20'><span>{{ $participant->participant->name() }}<span><small class='pl-1 text-muted'>{{ $participant->participant->sub_name() }}<small>" data-content="<img class='mr-2' src='{{ $participant->participant->logo() }}' width='20'><span>{{ $participant->participant->name() }}<small class='pl-1 text-muted'>{{ $participant->participant->sub_name() }}<small></span>" value="{{ route('competitions.stats', [$competition->season->slug, $competition->slug, $participant->participant->id]) }}">
						</option>
					@endforeach
				</select>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(function($) {
		$('#participant_selector').on('change', function() {
			var url = $(this).val();
			if (url) {
				window.location = url;
			}
			return false;
		});
	});
</script>