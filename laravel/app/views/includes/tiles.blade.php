<?php 
$tiles = CouchDB::getDoc("tiles", "misc");
$COMMON = Config::get('common'); 
?>
<h2>Tile Reference</h2>
@foreach ($tiles->tiles as $tile)
	{{--cycle through each tile to provide information about them--}}
	<p><img src='/img/Tiles/{{ $tile->file }}'> <b>{{ $tile->name }}</b> - Cost: <b> {{$tile->cost->{$COMMON['CURRENCY']} }}</b> <img src='{{ $COMMON['CURRENCY_IMG'] }}' class='currency' alt='{{ $COMMON['CURRENCY'] }}'><br>
	Description: <i>{{ $tile->desc }}</i><br>
	{{--tile specific sections--}}
	@if ($tile->effect->hp != 0)
		This tile will {{ (($tile->effect->hp < 0)? "deal <b>" . abs($tile->effect->hp) . "</b> damage":"heal <b>" . $tile->effect->hp . "</b> health") }} when you step on it.<br>
		This trap will activate {{ (($tile->effect->rearm == true)?"<b>multiple times</b>":"<b>once</b>") }}
	@else
		This tile deals <b>0</b> damage
	@endif
	 and is {{ (($tile->hidden == true)?"<b>hidden</b> until activated":"<b>always visible</b>") }}.<br>
	@if ($tile->effect->status == "none")
		It has no special status effects.<br>
	@else
		It has the status effect <b>{{ $tile->effect->status }}</b> which deals
		@if ($tiles->statuses->{$tile->effect->status}->effect < 0)
			<b>{{ abs($tiles->statuses->{$tile->effect->status}->effect) }}</b> damage per step.<br>
			@if ($tiles->statuses->{$tile->effect->status}->remove != "none")
				@if ($tiles->statuses->{$tile->effect->status}->remove == "death")
					The effect <b>{{ $tile->effect->status }}</b> can not be removed.<br>
				@else
					The effect <b>{{ $tile->effect->status }}</b> can be removed by tiles with the effect <b>{{ $tiles->statuses->{$tile->effect->status}->remove }}</b>.<br>
				@endif
			@endif
			
		@else
			no damage.<br>
		@endif
	@endif
	{{ (($tile->effect->blocking == true)?"This tile <b>blocks movement</b>":"") }}
	</p>
	

@endforeach