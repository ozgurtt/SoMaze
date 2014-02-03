@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Profile for {{ $user->nickname }} @include('includes.icon', array('status' => $user->status)) 
@stop

@section('content')
	This person is a <b>@include('includes.flair', array('status' => $user->status))</b> so be nice to them!<br>
	They've also been a member since {{ date("F j, Y, g:i a", $user->joined) }}
	<h3>Game Statistics</h3>
	<p>The following is a summary of the games they've played:
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
	<p>Puzzles they've made:<br>
	<ul>
	@if (count($user->games->creator) == 0)
		(none)
	@else
		@foreach ($user->games->creator as $k => $game)
    		<?php 
    		$puzzle = CouchDB::getDoc($game, "puzzles"); 
	    	$solved = (($puzzle->stats->solved == true)?"[Solved]":"[Unsolved]");
    		?>
    		<b>{{ $solved }}</b> - <a href='/play/{{ $puzzle->_id }}'>{{ $puzzle->title }}</a> - Entry: <b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
       		<br>
    		<b>Stats:</b> - <i>Attempts: </i>{{ $puzzle->stats->attempts }}
    		@if ($puzzle->stats->solved == true)
    			 - <i>Won by:</i> <a href='/profile/{{ $puzzle->stats->winner }}'>{{{ $puzzle->stats->winnick }}}</a> @include('includes.icon', array('status' => $puzzle->stats->winstatus))
    			  <i>on</i> {{ date("F j, Y, g:i a", $puzzle->stats->windate) }} CST
    		@endif
			<br><br>
		@endforeach
	@endif
	</ul>
	</p>

	@include('includes.gohome')
@stop