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
	@include('includes.tiles')
	</section>
@stop
