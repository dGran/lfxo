@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/competition/competition.css') }}" rel="stylesheet">
@endsection

@if ($group->phase->mode == 'league')
	@section('content')

		@include('competitions.partials.header')

		<div class="wrapper" style="background: #f9f9f9">
			@include('competitions.league.calendar.content')
		</div> {{-- wrapper --}}
	@endsection

	@section('breadcrumb')
		@include('competitions.league.calendar.breadcrumb')
	@endsection

	@section('bottom-fixed')
		@include('competitions.partials.bottom_fixed')
	@endsection

	@section('modal')
	    @include('competitions.league.calendar.update_modal')
	    @include('general_modals.match_details_modal')
	@endsection

	@section('js')
	    @include('competitions.league.calendar.javascript')
	    @include('general_js.match_details')
	@endsection
@endif