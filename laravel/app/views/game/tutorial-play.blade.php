@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	{{ $puzzle->order }}: {{ $puzzle->title }}
@stop

@section('content')
	<p><i>You have joined this game for free!</i></p>
	<p>{{ $puzzle->desc }}</p>
@stop

@section('div')
	<div id="game">
		<div id="hp">
		</div>
		<div id="healthbar">
		</div>
		<div id="statusbar">
		</div>
	</div>
	<div id="itembar">
	</div>
	<div id="alerts">
	</div>
	<div id="newtiles">
	@foreach($puzzle->newtiles as $tile)
		<p>{{ Shared\Game::buildInfo($tile, false) }}</p>
	@endforeach
	</div>
@stop

@section('js')
	<script src="/js/tutorial.js"></script>
@stop

@section('snippet')
	<script>
		var GAME_ID ='{{{ $puzzle->_id }}}';
	</script>
@stop