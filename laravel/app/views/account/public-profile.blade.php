@extends('layouts.main')

@section('heading')
	Profile for {{ $user->nickname }}
@stop

@section('content')
	Here's some information about this person.
	@include('includes.gohome')
@stop