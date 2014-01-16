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
	</form>	<br>
	<h3>Open Games</h3>
	<p>Active puzzles you've made:<br>
	<ul>
	@if (count($user->games->creator) == 0)
		(none)
	@else
		@foreach ($user->games->creator as $name => $game)
    		<a href='/play/{{ $name }}'>{{ $name }}</a>
		@endforeach
	@endif
	</ul>
	</p>
	<p>Active puzzles you're solving:<br>
	<ul>
	<?php $solverGames = get_object_vars($user->games->solver); ?>
	@if (count($solverGames) == 0)
		(none)
	@else
		@foreach ($solverGames as $name => $game)
    		<a href='/play/{{ $name }}'>{{ $name }}</a><br>
		@endforeach
	@endif
	</ul>
	</p>	
@stop