@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/clubs/clubs.css') }}" rel="stylesheet">
@endsection

@section('content')

	@include('clubs.partials.header')

	<div class="wrapper" style="background: #f9f9f9">
		@include('clubs.press.data')
	</div>

@endsection

@section('breadcrumb')
	@include('clubs.press.breadcrumb')
@endsection

@section('bottom-fixed')
	@include('clubs.partials.bottom_fixed')
@endsection

@section('js')
	@include('clubs.press.javascript')
@endsection