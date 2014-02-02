@extends('layouts.main')
<?php 
$COMMON = Config::get('common');
$COINS = Config::get('coins');
?>

@section('heading')
	My Wallet
@stop

@section('content')
	<h3>Funds</h3>
	<p>Here is a summary of the funds you have in your wallet:
	<?php $balance = Coins\Dogecoin::getBalance($user->_id); ?>
	<ul>
	<li>Available: <b>{{ $balance['available'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	<li>Pending: <b>{{ $balance['pending'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	<li>Locked: <b>{{ $balance['locked'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'></li>
	</ul>
	<h3>Deposit Address</h3>
	<p>Deposit Address: <b>
	@if (Input::has('newAddress'))
		{{{ Input::get('newAddress') }}}
	@else
		{{ Coins\Dogecoin::getAccountAddress($user->_id) }}
	@endif
	</b><br>
	<a href="{{ action('UserController@getNewAddress') }}">Generate New Address</a></p>
	<h3>Withdraw Funds</h3>
	<form role="form" action="{{ action('UserController@withdraw') }}" method="post">
	<fieldset>
	<div class="form-group">
		<label for="address">Address</label>  
		<input id="address" name="address" type="text" placeholder="Wallet Address" class="form-control" required="true">
	</div>
	<div class="form-group">
		<label for="amount">Amount</label>  
		<input id="amount" name="amount" type="text" placeholder="Amount to Withdraw" class="form-control" required="true">
	</div>
	  <p>All payments sent will be subject to a <b>{{ $COINS[$COMMON['CURRENCY']]['TX_FEE'] }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> transaction fee.</p>

	  <button id="submit" name="submit" class="btn btn-primary">Submit Withdraw Request</button>
	</fieldset>
	</form>
	<hr>
	<h3>Transaction History</h3>
	{{ json_encode($data = Coins\Dogecoin::listTransactions($user->_id, 30)) }}
	
	
	@include('includes.goaccount')
@stop