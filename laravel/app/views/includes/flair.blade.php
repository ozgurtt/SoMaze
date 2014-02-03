@if ($status->staff)
	Staff Member
@elseif ($status->donator)
	Donator
@elseif ($status->vip)
	VIP
@elseif ($status->verified)
	Verified User
@else
	User
@endif