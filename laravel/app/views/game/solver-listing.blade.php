@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
    {{ ((count($results->rows) == 1)?"There is currently 1 game to join":"There are currently " . count($results->rows) . " games to join") }}
@stop

@section('div')
    @foreach ($results->rows as $row)
    	<a href='{{ action('GameController@confirmEntry', array('id' => $row->id)) }}' class='list-group-item'>
    	<div class="well well-sm"><i>
    	<?php 
	    	switch ($row->value[6]->attempts){
		    	case 0:
		    		$attempts = "No one has attempted this, you should be the first!";
		    		break;
		    	case 1:
		    		$attempts = "1 user has attempted this " . Shared\Common::timeElapsed($row->value[6]->last) . " ago.";
		    		break;
		    	default:
		    		$attempts = $row->value[6]->attempts . " users have attempted this, the most recent happened " . Shared\Common::timeElapsed($row->value[6]->last) . " ago.";
		    		break;
	    	}
    	?>
    	{{ $attempts }}
    	</i></div>
	    	<span class='badge'>
				Entry: {{ $row->value[5]->entry }} <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			  - Reward: {{ $row->value[5]->reward }} <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
			</span>
			{{ $row->value[1] }} by {{ $row->value[0]->nickname }} @include('includes.icon', array('status' => $row->value[0]->status))
			<br>Dimensions: {{ $row->value[3]->width }}x{{ $row->value[3]->height }}
			<?php $difficulty = Shared\Game::getDifficulty($row->value[3], $row->value[4]); ?>
			<br>Difficulty: {{ $difficulty['difficulty'] }}% 
			<span class="label {{ $difficulty['label'] }}">{{ $difficulty['note'] }}</span>
    	</a>
	@endforeach
	@if (count($results->rows) == 0)
		There aren't any games created at the moment, you should click "Create" up top and make one to share with the world.  Don't be afraid, I have faith in you.
	@endif
@stop