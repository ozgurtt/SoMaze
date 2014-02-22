@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
    {{ (($count == 1)?"There is currently 1 tutorial to play":"There are currently " . $count . " tutorials to play") }}
@stop

@section('content')
	The following tutorials will help you learn the mechanics of the game.  It's strongly suggested that you play them in order.
@stop

@section('div')
    @foreach ($results as $k => $row)
    	<a href='{{ action('GameController@playTutorial', array('id' => $row->id)) }}' class='list-group-item'>
	    	<span class='badge'>
				Free to play!
			</span>
			<b>{{ $k+1 }}: {{ $row->value[0] }}</b>
			<br>Dimensions: {{ $row->value[1]->width }}x{{ $row->value[1]->height }}
    	</a>
	@endforeach
	@if ($count == 0)
		There aren't any tutorials created at the moment.
	@endif
@stop