@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	{{ $puzzle->title }} by {{ $puzzle->creator->nickname }} @include('includes.icon', array('status' => $puzzle->creator->status))

@stop

@section('content')
	@if ($puzzle->creator->id == $user->_id)
		<p><i>You have joined this game for free (you are the creator).</i></p>
	@elseif ($amount == 0)
		{{--they already paid--}}
		<p><i>You have rejoined this game for free.</i></p>
	@else
		{{--they are paying right now--}}
		<p><i>You just paid <b>{{ $amount }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> to join this game</i></p>
	@endif
	<p>{{ $puzzle->desc }}</p>
@stop

@section('div')
	<div id="game">
		<div id="hp">
		</div>
		<div id="healthbar">
		</div>
	</div>
	<div id="alerts">
	</div>
	<div class="panel panel-default">
	  <div class="panel-heading">
	    <h3 class="panel-title">Puzzle Statistics</h3>
	  </div>
	  <div class="panel-body">
	    <p>
			Difficulty: {{ $difficulty['difficulty'] }}% 
			<span class="label {{ $difficulty['label'] }}">{{ $difficulty['note'] }}</span>
	    </p>
	    <table id='fee'>
	    <tr><td class='fee'>Creation Fee</td><td><b>{{ $puzzle->fees->creation }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></td></tr>
	    <tr><td class='fee'>Entry Fee</td><td><b>{{ $puzzle->fees->entry }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></td></tr>
	    <tr><td class='fee'>Reward Fee</td><td><b>{{ $puzzle->fees->reward }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></td></tr>
	    </table>
	  </div>
	</div>
@stop

@section('js')
	<script src="/js/game.js"></script>
@stop

@section('snippet')
	<script>
		var GAME_ID ='{{{ $game->gameid }}}';
		var sessionID ='{{{ $game->sessionID }}}';
	</script>
@stop