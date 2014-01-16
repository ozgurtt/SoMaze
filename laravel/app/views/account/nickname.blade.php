@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Account settings
@stop

@section('content')
	<p>Your nickname has been changed to <b>{{{ $nickname }}}</b></p>
	@include('includes.goaccount')
@stop