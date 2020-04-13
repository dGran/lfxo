<div class="bottom-fixed">
	<div class="container">
		<div class="scrolling-wrapper">
			@foreach ($participants as $part)
				@if ($part->team)
					<div class="card participants {{ $part->id == $participant->id ? 'active' : ''}}">
						<a href="{{route(\Route::current()->getName(), [$season_slug, $part->team->slug]) }}">
							<img src="{{ $part->logo() }}" alt="{{ $part->name() }}">
							<span>{{ $part->name() }}</span>
						</a>
					</div>
				@endif
			@endforeach
		</div> {{-- scrolling-wrapper --}}
	</div> {{-- container --}}
</div> {{-- bottom-fixed --}}