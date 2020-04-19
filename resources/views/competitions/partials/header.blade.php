<div class="competition-header">
	<div class="container">
		<div class="logo">
			<img src="{{ asset($competition->getImgFormatted()) }}" width="55" class="rounded">
		</div>
		<div class="title">
    		<h3>
    			{{ $competition->name }}
    		</h3>
    		<span class="subtitle">
    			{{ $competition->season->name }}
    		</span>
		</div>
	</div>
</div>

<div class="competition-menu">
	<div class="container">
		@include('competitions.partials.menu')
	</div>
</div>

@if (\Route::current()->getName() != 'competitions.stats')
	@include('competitions.partials.phase_group_selector')
@endif
