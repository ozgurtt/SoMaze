@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Creation summary
@stop

@section('content')
	Are you sure you want to create this puzzle?</p>
	<p>You will instantly pay <b>{{ $fees->creation }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> as a creation fee.<br>
	You will have <b>{{ $fees->reward }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> locked and made unavilable to you for the reward.<br>
	You will be charging <b>{{ $fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> to users wishing to play your puzzle<br>
		 <br>(you currently have <b>{{ $wallet['available'] }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
		 available)<br><br>
		Are you sure you want to create this puzzle?  As a reminder, you will have to solve your puzzle before other users can play it.</p>
	<p>
		<a href="{{ action('GameController@createPuzzle') }}" class="btn btn-danger btn-lg">No</a>
		<a href="{{ action('GameController@createResponse') }}" class="btn btn-success btn-lg">Yes</a>
	</p>				
</p>
@stop