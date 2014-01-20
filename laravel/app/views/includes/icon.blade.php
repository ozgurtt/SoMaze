@if ($status->staff)
	<span class="glyphicon glyphicon-flash" title="Staff"></span>
@elseif ($status->donator)
	<span class="glyphicon glyphicon-heart" title="Donator"></span>
@elseif ($status->vip)
	<span class="glyphicon glyphicon-star" title="VIP"></span>
@elseif ($status->verified)
	<span class="glyphicon glyphicon-ok" title="Verified"></span>
@else
	<span class="glyphicon glyphicon-user" title="User"></span>
@endif