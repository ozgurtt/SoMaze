@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
    {{ (($count == 1)?"There is currently 1 game to join":"There are currently " . $count . " games to join") }}
@stop

@section('content')
	@if ($count != 0)
		@foreach ($sorts as $k => $val)
			<?php 
			$order = explode("-", $k);
			$glyph = (($order[1] == "asc") ? "glyphicon glyphicon-chevron-up" : "glyphicon glyphicon-chevron-down");
			?>
			
			@if ($k == $sort)
				<a href="{{ action('GameController@showGameListing', array('page' => $results->getCurrentPage(), 'sort' => $k )) }}" class="btn btn-info"><span class="{{ $glyph }}"></span> {{ $val }}</a>
			@else
				<a href="{{ action('GameController@showGameListing', array('page' => $results->getCurrentPage(), 'sort' => $k )) }}" class="btn btn-primary"><span class="{{ $glyph }}"></span> {{ $val }}</a>
			@endif
		@endforeach
	@endif

@stop

@section('div')
    @foreach ($results as $row)
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
		    		$attempts = $row->value[6]->attempts . " attempts have been made at this puzzle, the most recent happened " . Shared\Common::timeElapsed($row->value[6]->last) . " ago.";
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
	@if ($count == 0)
		There aren't any games created at the moment, you should click "Create" up top and make one to share with the world.  Don't be afraid, I have faith in you.
	@else
		{{ $results->appends(array('sort' => $sort))->links() }}
	@endif
@stop