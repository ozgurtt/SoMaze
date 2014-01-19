@if ($status->staff)
	(Staff)
@elseif ($status->donator)
	(Donator)
@elseif ($status->vip)
	(VIP)
@elseif ($status->verified)
	(Verified)
@else
	(User)
@endif