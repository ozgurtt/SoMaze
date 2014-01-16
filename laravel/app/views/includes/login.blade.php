@if (Session::has('user'))
	<p class='navbar-text navbar-right'>Signed in as <a href='{{ action('UserController@accountIndex') }}' class='navbar-link'>{{ Session::get('nickname') }}</a> - <a href='{{ action('LoginController@doLogout') }}' class='navbar-link'>Logout</a></p>
@else
	<div class="navbar-form navbar-right">
		<a class="btn btn-success" href="{{ action('LoginController@doLogin') }}">Sign in with Google OpenID</a>
	</div>
@endif



