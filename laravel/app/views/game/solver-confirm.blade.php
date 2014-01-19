@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Entry confirmation
@stop

@section('content')
<p class="lead">
	You are getting ready to join <b>{{{ $puzzle->title }}}</b> by <b>{{{ $puzzle->creator->nickname }}} @include('includes.icon', array('status' => $puzzle->creator->status))
</b></p>
{{--if they are the creator, don't charge them anything--}}
	@if (in_array($puzzle->_id, $user->games->creator))
		@if ($puzzle->active == false)
			@if ($puzzle->stats->solved == false)
				<p>Your puzzle isn't active yet and needs to be successfully solved by you first, are you ready to solve it?</p>
			@else
				<p>Your puzzle has been beaten and can't be reactivated at this time.  Do you still want to play it?</p>
			@endif
		@else
			<p>Your puzzle is already active and doesn't require any interaction from you to make it work. Do you still want to play it?</p>
		@endif
		<p>You will not be charged anything for playing this puzzle</p>
	@else
		@if (isset($user->games->solver->{$puzzle->_id}))
			<p>You have already paid the entry fee of <b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> and you already have an open session in this game.  <br>Would you like to rejoin it?  This action will not cost you anything.</p>
		@else
			<p>To attempt this puzzle will cost you an entry fee of <b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			 (you currently have <b>{{ $user->wallet->available }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			 available)<br>
			Are you sure you want to pay this?
		</p>
		@endif
	@endif
	<p>
		<a href="{{ action('GameController@showGameListing') }}" class="btn btn-danger btn-lg">No</a>
		<a href="{{ action('GameController@playResponse', array('id' => $puzzle->_id)) }}" class="btn btn-success btn-lg">Yes</a>
	</p>				
</p>
@stop