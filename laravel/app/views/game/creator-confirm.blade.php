@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Creation summary
@stop

@section('content')
	Are you sure you want to create this puzzle?</p>
	<p>You will instantly pay <b>{{ $fees->creation }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> as a creation fee.<br>
	You will have <b>{{ $fees->reward }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> locked and made unavilable to you for the reward.<br>
	You will be charging <b>{{ $fees->entry }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> to users wishing to play your puzzle<br>
		 <br>(you currently have <b>{{ $wallet['available'] }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'>
		 available)<br><br>
		Are you sure you want to create this puzzle?  As a reminder, you will have to solve your puzzle before other users can play it.</p>
	<p>
		<a href="{{ action('GameController@createPuzzle') }}" class="btn btn-danger btn-lg">No</a>
		<a href="{{ action('GameController@createResponse') }}" class="btn btn-success btn-lg">Yes</a>
	</p>	
	<div class="panel panel-info">
	<div class="panel-heading">
	<h3 class="panel-title">Profit Information</h3>
	</div>
	<div class="panel-body">
	<?php 
	function getProfit($plays, $fees){
		//gets profit from plays
		$gross = $plays * $fees->entry;
		$net = $gross - ($fees->creation * $plays) - $fees->creation - $fees->reward;
		return $net;
	}
	function getBreakEven($fees){
		//gets profit from plays
		$income = $fees->entry - $fees->creation;
		$games = ceil(($fees->creation + $fees->reward) / $income);
		return $games;
	}
	$i = getBreakEven($fees);
	$j = 0;
	?>
	You will break even after <b>{{ getBreakEven($fees) }}</b> games.<br>
	
	The following schedule shows how much profit you'll make with the fees and rewards you've set if your puzzle is solved after these many plays:<br>
	<ul>
	
	@while ($j <= 2)
		<li>After <b>{{ $i }}</b> games: <b>{{ getProfit($i, $fees) }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
		<?php $j++; $i+=10;?>
	@endwhile
	</ul>
	</div>
	</div>				
</p>
@stop