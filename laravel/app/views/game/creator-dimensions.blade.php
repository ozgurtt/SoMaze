@extends('layouts.main')

@section('heading')
	Puzzle creation
@stop

@section('content')
	To get started creating your puzzle, set the dimensions for it<br>
	<form role="form" action="make" method="get">
	<div class="form-group">
		<table>
		<tr>
		<?php 
			$GAME = Config::get('game');
			$pattern = ".{" . $GAME['MIN_PUZZLE_SIZE'] . "," . $GAME['MAX_PUZZLE_SIZE'] . "}";
			$title = "Valid ranges for dimensions are " . $GAME['MIN_PUZZLE_SIZE'] . " to " . $GAME['MAX_PUZZLE_SIZE'];
		?>
		<td><label for="width">Width</label></td>
		<td><input type="number" id="width" name="width" min="{{ $GAME['MIN_PUZZLE_SIZE'] }}" max="{{ $GAME['MAX_PUZZLE_SIZE'] }}" input pattern="{{ $pattern }}" title="{{ $title }}"></td>
		</tr>
		<tr>
		<td><label for="height">Height</label></td>
		<td><input type="number" id="height" name="height" min="{{ $GAME['MIN_PUZZLE_SIZE'] }}" max="{{ $GAME['MAX_PUZZLE_SIZE'] }}"  input pattern="{{ $pattern }}" title="{{ $title }}"></td>
		</tr>
		</table><br>
		<button class="btn btn-success btn-lg">Get Started!</button>
	</div>
</form>
@stop