@extends('layouts.main')

@section('heading')
    About SoMaze
@stop

@section('content')
	{{ $readme }}
	<br><br>
	@include('includes.tiles')
@stop