@extends('layouts.main')

@section('heading')
    About SoMaze
@stop

@section('content')  
	<a href="#howtoplay">[How To Play]</a> - <a href="#faq">[FAQ]</a> - <a href="#tiles">[Tile Reference]</a>
	<hr>
	<section class="about" id='howtoplay'>
		<h2>How to Play</h2>
		<p>To be added</p>
	</section>
	<section class="about" id='faq'>
		<h2>FAQ</h2>
		<p>
			<b>Q.</b> What's this game all about<br>
			<b>A.</b> It's about mazes and stuff
		</p>
	</section>
	<section class='about' id='tiles'>
		<h2>Tile Reference</h2>
		<?php $tiles = \CouchDB::getDoc("tiles", "misc"); ?>
		@foreach ($tiles->tiles as $k => $tile)
			{{--cycle through each tile to provide information about them--}}
			<section id="tile{{ $k }}" class="tiles">
				{{ Shared\Game::buildInfo($k) }}
			</section>
		@endforeach
	</section>
@stop
