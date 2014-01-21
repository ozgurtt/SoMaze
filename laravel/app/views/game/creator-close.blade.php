@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Puzzle closed
@stop

@section('content')
	Your puzzle has been successfully closed!<br><br>
	Name: <b>{{ $puzzle->title }}</b><br>
	<br>
	You were refunded <b>{{ $amount }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> for the reward.<br>
	You made <b>{{ $profit }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> profit on this puzzle.<br>
	<br>
	@include('includes.goaccount')
@stop