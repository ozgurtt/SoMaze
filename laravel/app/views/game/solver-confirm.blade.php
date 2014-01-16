@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Entry confirmation
@stop

@section('content')
<p class="lead">
	You are getting ready to join <b>{{{ $puzzle->title }}}</b> by <b>{{{ $puzzle->nickname }}}</b></p>
	@if (isset($user->games->solver->{$puzzle->_id}))
		<p>You have already paid the entry fee of <b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> and you already have an open session in this game.  <br>Would you like to rejoin it?  This action will not cost you anything.</p>
	@else
		<p>To attempt this puzzle will cost you an entry fee of <b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
		 (you currently have <b>{{ $user->wallet->available }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
		 available)<br>
		Are you sure you want to pay this?
	</p>
	@endif
	<p>
		<a href="{{ action('GameController@showGameListing') }}" class="btn btn-danger btn-lg">No</a>
		<a href="{{ action('GameController@playResponse', array('id' => $puzzle->_id)) }}" class="btn btn-success btn-lg">Yes</a>
	</p>				
</p>
@stop