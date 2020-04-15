<div class="bottom-fixed">
	<div class="container">
		<div class="scrolling-wrapper">
			@foreach ($competitions as $comp)
				@if ($comp->isConfigured())
					<div class="card competition {{ $comp->slug == $competition->slug ? 'active' : ''}}">
						{{-- \Route::current()->getName() --}}
						<a href="{{route(\Route::current()->getName(), [$comp->season->slug, $comp->slug]) }}">
							<img src="{{ $comp->getImgFormatted() }}" alt="{{ $comp->name }}" class="rounded">
							<span>{{ $comp->name }}</span>
						</a>
					</div>
				@endif
			@endforeach
		</div> {{-- scrolling-wrapper --}}
	</div> {{-- container --}}
</div> {{-- bottom-fixed --}}