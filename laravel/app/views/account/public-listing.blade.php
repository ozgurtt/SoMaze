@extends('layouts.main')

@section('heading')
	Profile Listings
@stop

@section('content')
	The following is a list of all registered users:
	@foreach ($results as $row)
		<p><a href='/profile/{{ $row->id }}'>{{ $row->key }}</a></p>
	@endforeach
	@if ($count == 0)
		There aren't any users registered at the moment, you should be the first!
	@else
		{{ $results->links() }}
	@endif
	@include('includes.gohome')
@stop