@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div id="wrapper" style="background: #171d21">

        @include('home.banner')
        {{-- @include('home.countdown') --}}
        {{-- @include('home.top_section') --}}
        @include('home.last_news')
        @include('home.last_users')
        @include('home.testimonies')
        @include('home.bottom_section')

    </div> {{-- wrapper --}}
@endsection

@section('js')
	@include('home.javascript')
@endsection