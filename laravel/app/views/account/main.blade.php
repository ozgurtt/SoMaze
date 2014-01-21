@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Account settings
@stop

@section('content')
	<p>Your nickname is currently <b>{{{ Session::get('nickname') }}}</b> <i>(which we all love)</i><br>To change it, type in your desired nickname in the box below and click submit.</p>
	<form role="form" action="/account/nickname" method="post">
	<div class="form-group">
    	<label for="nickname">Nickname</label>
		<input type="text" class="form-control" name="nickname" placeholder="{{{ Session::get('nickname') }}}">
	</div>
		<button type="submit" class="btn btn-primary">Change Nickname</button>
	</form>	
	<h3>Flair</h3>
	<p>You are currently displaying flair for your status, which looks like this: @include('includes.icon', array('status' => $user->status)) 
	@include('includes.flair', array('status' => $user->status))

	
	<br>
	<h3>Open Games</h3>
	<p>Puzzles you've made:<br>
	<ul>
	@if (count($user->games->creator) == 0)
		(none)
	@else
		@foreach ($user->games->creator as $k => $game)
    		<?php 
    		$puzzle = CouchDB::getDoc($game, "puzzles"); 
	    	$solved = (($puzzle->stats->solved == true)?"[Solved]":"[Unsolved]");
	    	$gross = $puzzle->stats->attempts * $puzzle->fees->entry;
	    	$net = ($gross - ($puzzle->stats->attempts * $puzzle->fees->creation)) - $puzzle->fees->reward - $puzzle->fees->creation;
    		?>
    		<b>{{ $solved }}</b> - <a href='/play/{{ $puzzle->_id }}'>{{ $puzzle->title }}</a> - Profit: <b>{{ $net }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
    		@if ($puzzle->stats->attempts == 0 || $puzzle->stats->solved == true)
    			- Actions: <a href='/account/close/{{ $puzzle->_id }}'>[close]</a>
    		@endif
    		<br>
    		<b>Stats:</b> - <i>Attempts: </i>{{ $puzzle->stats->attempts }}
    		@if ($puzzle->stats->solved == true)
    			 - <i>Won by:</i> {{ $puzzle->stats->winnick }}@include('includes.icon', array('status' => $puzzle->stats->winstatus))
    			  <i>on</i> {{ date("F j, Y, g:i a", $puzzle->stats->windate) }} CST
    		@endif
			<br><br>
		@endforeach
	@endif
	</ul>
	</p>
	<p>Puzzles you're solving:<br>
	<ul>
	<?php $solverGames = get_object_vars($user->games->solver); ?>
	@if (count($solverGames) == 0)
		(none)
	@else
		@foreach ($solverGames as $name => $game)
    		<?php $puzzle = CouchDB::getDoc($name, "puzzles"); ?>
    		<a href='/play/{{ $puzzle->_id }}'>{{ $puzzle->title }}</a> by: {{ $puzzle->creator->nickname }} @include('includes.icon', array('status' => $puzzle->creator->status)) <i>[Reward: {{ $puzzle->fees->reward }}<img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>]</i><br>
		@endforeach
	@endif
	</ul>
	</p>	
@stop