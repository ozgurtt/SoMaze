@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
    {{ ((count($results->rows) == 1)?"There is currently 1 game to join":"There are currently " . count($results->rows) . " games to join") }}
@stop

@section('div')
    @foreach ($results->rows as $row)
    	<a href='{{ action('GameController@confirmEntry', array('id' => $row->id)) }}' class='list-group-item'>
	    	<span class='badge'>
				Entry: {{ $row->value[5]->entry }} <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			  - Reward: {{ $row->value[5]->reward }} <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			</span>
			{{ $row->value[1] }} by {{ $row->value[0] }}
			<br>Dimensions: {{ $row->value[3]->width }}x{{ $row->value[3]->height }}
			<?php $difficulty = Shared\Game::getDifficulty($row->value[3], $row->value[4]); ?>
			<br>Difficulty: {{ $difficulty['difficulty'] }}% 
			<span class="label {{ $difficulty['label'] }}">{{ $difficulty['note'] }}</span>
    	</a>
	@endforeach
@stop