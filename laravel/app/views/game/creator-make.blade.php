@extends('layouts.main')
<?php $GAME = Config::get('game'); ?>

@section('heading')
	Puzzle creation
@stop

@section('content')
	To create a puzzle, click on the tile you want in the library, and after you do, click on all the tiles you want to look like that on your puzzle.  When you are done, click the 'Next Step' to continue
@stop

@section('div')
	<div id="game">
	</div>
	<div id="alerts">
	</div>
	<div id="tiles">
	<div id="tileinfo">
	Select a tile
	</div>
	</div>
	<br>
	<button id="nextstep" class="btn btn-primary btn-lg">Next Step</button><br>
	<div id="metaform">
	<form role="form" action="/make/summary" method="post">
		<div class="form-group">
	    	<label for="title">Title of the puzzle</label>
			<input type="text" class="form-control" name="title" placeholder="{{{ Session::get('nickname') }}}'s super awesome puzzle" input pattern=".{3,100}" title="3 to 100 characters">
		</div>
		<div class="form-group">
	    	<label for="desc">Description for the puzzle</label>
			<input type="text" class="form-control" name="desc" placeholder="A super awesome puzzle that's made by a super awesome person." input pattern=".{3,1000}" title="3 to 1000 characters">
		</div>
		<div id="feeform">
			<div class="form-group">
			<table>
			<tr>
			<td><b>Creation Fee (paid by you)</b></td>
			<td><div id='creationfee'>0</div></td>
			</tr>
			<tr>
			<td><label for="entry">Entry Fee (paid by the player)</label></td>
			<td><input type="number" id="entry" name="entry" min=0></td>
			</tr>
			<tr>
			<td><label for="reward">Reward (paid by you)</label></td>
			<td><input type="number" id="reward" name="reward" min=0></td>
			</tr>
			</table><br>
	</div>		
		</div>
			<button type="submit" class="btn btn-success btn-lg">Finalize</button>
		</form>
	</div>
@stop

@section('js')
	<script src="/js/create.js"></script>
@stop

@section('snippet')
	<script>
		{{--these values come from user input, escape them, jussssst in case--}}
		var WIDTH ='{{{ $width }}}';
		var HEIGHT ='{{{ $height }}}';
	</script>
@stop