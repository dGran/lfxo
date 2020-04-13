@extends('layouts.app')

@section('style')
    <style>
		.profile-header {
			background: #252B31;
			margin-top: 55px;
			padding: .75rem .25rem;
		}
		.profile-header h3 {
			color: #fff;
			font-size: 1.5em;
			margin: 0;
		}
		.profile-header img {
			width: 65px;
		}
		.profile-header .subname {
			color: #B2B2B2;
			display: block;
			font-weight: bold;
			font-size: .8em;
		}
		.wrapper {
			background: #f9f9f9;
		}
    </style>
@endsection

@section('content')
	@include('users.profile.header')

	<div class="wrapper">
		<div class="container">
			<div class="row">
				<div class="col-12">
					@include('users.profile.content')
				</div>
			</div>
		</div>
	</div>
@endsection

@section('breadcrumb')
	@include('users.profile.breadcrumb')
@endsection
