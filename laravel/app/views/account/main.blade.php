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
		<input type="text" class="form-control" name="nickname" required="true" placeholder="{{{ Session::get('nickname') }}}" input pattern=".{3,100}" title="3 to 100 characters">
	</div>
		<button type="submit" class="btn btn-primary">Change Nickname</button>
	</form>	
	<h3>My Wallet</h3>
	<p>Here is a summary of the funds you have in your wallet:
	<?php $balance = Coins\Dogecoin::getBalance($user->_id); ?>
	<ul>
	<li>Available: <b>{{ $balance['available'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	<li>Pending: <b>{{ $balance['pending'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	<li>Locked: <b>{{ $balance['locked'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	</ul>
	<a href="/account/wallet" class="btn btn-primary"><span class="glyphicon glyphicon-wrench"></span> Manage My Wallet</a>
	<h3>Flair</h3>
	<p>You are currently displaying flair for your status, which looks like this: @include('includes.icon', array('status' => $user->status)) 
	@include('includes.flair', array('status' => $user->status))
	</p>
	<h3>Game Statistics</h3>
	<p>The following is a summary of the games you've played:
	<ul>
	<li>Attempts: <b>{{ $user->stats->attempts }}</b></li>
	<li>Wins: <b>{{ $user->stats->wins }}</b></li>
	<li>Losses: <b>{{ $user->stats->losses }}</b></li>
	<li>Win Percentage: 
	@if ($user->stats->attempts != 0)
		<b>{{ round(($user->stats->wins / $user->stats->attempts)*100, 2) }}%</b>
	@else
		<b>0%</b>
	@endif
	</li>
	</ul>
	</p>
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
    		<?php 
    		$puzzle = CouchDB::getDoc($name, "puzzles"); 	
    		?>
    		<a href='/play/{{ $puzzle->_id }}'>{{ $puzzle->title }}</a> by: {{ $puzzle->creator->nickname }} @include('includes.icon', array('status' => $puzzle->creator->status)) <i>[Reward: {{ $puzzle->fees->reward }}<img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>]</i><br>
		@endforeach
	@endif
	</ul>
	</p>	
@stop