@extends('layouts.main')

@section('heading')
	Login Success
@stop

@section('content')
	<p>You've successfully logged in!</p>
	@if ($nickname != null)
		<div class="panel panel-default">
		<div class="panel-body">
		Google doesn't provide us with a cool nickname for you to use, and since we figure you don't want to use your real name, we have provided you with a super awesome nickname to use for now.  You can feel free to change it in your account settings if you'd like.  Although honestly, why would you want to?<br><br>
		<b>Nickname: {{ $nickname }}</b>
		</div>
		</div>
		</p>
	@endif
	@include('includes.gohome')
@stop