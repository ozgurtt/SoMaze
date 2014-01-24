@extends('layouts.main')
<?php $COMMON = Config::get('common'); ?>

@section('heading')
	Puzzle saved
@stop

@section('content')
	Your puzzle has been successfully saved!<br>
	ID: <a href='{{ action('GameController@confirmEntry', array('id' => $id)) }}'>{{ $id }}</a><br>
	<br>
	You paid <b>{{ $creation }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> for the creation fee<br>
	<b>{{ $reward }}</b><img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'> is locked for the reward<br><br>
	You must play your puzzle and solve it before it will be active.  <br>You can play your newly created puzzle from the above link, or from the link in your account settings (under 'Puzzles you've made').
@stop