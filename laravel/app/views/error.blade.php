@extends('layouts.main')

@section('heading')
	{{ $error }}
@stop

@section('content')
	<p>{{ $content }}</p>
	@include('includes.gohome')
@stop