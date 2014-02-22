@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	My Wallet
@stop

@section('content')
	<p>Your withdraw request has been processed<br>
		Amount: <b>{{ $amount }}</b><br>
		Address: <b>{{ $address }}</b><br>
		Transaction ID: <b>transaction id goes here</b>
	</p>
	@include('includes.goaccount')
@stop