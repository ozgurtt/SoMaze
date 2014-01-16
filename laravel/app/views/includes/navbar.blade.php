<ul class="nav navbar-nav">
	<li><a href="/">Home</a></li>
	<li><a href="/about">About</a></li>
	<li><a href="/contact">Contact</a></li>
	<li><a href="/play">Play</a></li>
@if (Session::has('user'))
	<li><a href='/create'>Create</a></li>
	<li><a href='/account'>Account</a></li>
@endif
</ul>


