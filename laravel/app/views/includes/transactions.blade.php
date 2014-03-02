<?php
	$i = count($transactions);
	$g = 0;
	$COINS = Config::get('coins');
?>

<h3>{{$i}} Recent Transactions</h3>
	
@while ($i > 0)
	<?php $i--; ?>
	@if ($transactions[$i]['category'] == "receive")
		 <p>You received <b>{{ $transactions[$i]['amount']}}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> from address <b>{{$transactions[$i]['address']}}</b> {{ \Shared\Common::timeElapsed($transactions[$i]['time']) }} ago</p>	@elseif ($transactions[$i]['category'] == "send")
		<p>You sent <b>{{ abs($transactions[$i]['amount'])}}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> <i>(with <b>{{abs($transactions[$i]['fee'])}}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> fee)</i> to address <b>{{$transactions[$i]['address']}}</b> {{ \Shared\Common::timeElapsed($transactions[$i]['time']) }} ago</p>
	@else
		{{--if it's not sending or reeiving, do we really care?--}}
		<?php $g++; ?>
	@endif
	
@endwhile

@if ($g != 0)
	<p><b>{{$g}}</b> internal game related transactions</p>
@endif
<hr>