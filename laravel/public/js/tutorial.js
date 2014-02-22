var lastClicked;
var lastTile;
var startTile = null;

var tileData;
var puzzleData;
var fullPuzzleData;

var hp = 0;
var hearts = 10;

var itemsHUD = [];
var itemsEquip = [];

var serverStatus = [];
var serverMovechain = [];

var locked = false;

$( document ).ready(function() {
    console.log("DOM loaded");
    $.getJSON( "/api/v1/all/getTiles/", function( data ) {
		//we got the tile, now get the game
		tileData = data;
		$.getJSON( "/api/v1/tutorial/getMap/"+GAME_ID, function( data ) {
			puzzleData = data.clean;
			fullPuzzleData = data.puzzle;
			//console.log(mapData);
			var grid = clickableGrid(puzzleData.dimensions.height,puzzleData.dimensions.width,function(el,row,col,i){
			    console.log("You clicked on item #:",i);
				if (validateClick(lastTile, i, col) && checkBlocking(i) == false){
					console.log ("validate passed");
					sendMove(i, el);
				}else{console.log("validate failed");}
			});
			//document.body.appendChild(grid);
			$("#game").append(grid);
			$("#gameGrid").on('dragstart', function(event) { event.preventDefault();});
			populateHUD(data.items);
			updatePlayer(data);
			//for Caleb's keypresses
			$('html').keydown(function(e){
				e.stopPropagation();
				e.preventDefault();
				keyMove(e.which);
		    });
		});
	});
    
});

function updatePlayer(data){
	if (data.hp != hp){
    	//redraw only if there's a change, this prevents flickering
	    $("#healthbar").html(getHearts(data.hp));
    }
    hp = data.hp;
    $("#hp").html("<p>HP: " + ((hp <0)?0:hp) + "</p>");
    $("#statusbar").html(getStatus(data.status));
    $("#itembar").html(refreshHUD());
}

function populateHUD(items){
	//run to populate the HUD
	for (var i=0;i<tileData.tiles.length;++i){
		if (typeof tileData.tiles[i].item != 'undefined'){
			if (typeof tileData.tiles[i].item.equip != 'undefined'){
				//this tile is a key
				if (jQuery.inArray(i, puzzleData.map) > -1){
					//it's not already in the hud, so let's add it
					itemsHUD.push(tileData.tiles[i].item.equip);
				}
			}
		}
	}
	for (var i=0;i<items.length;++i){
		itemsEquip.push(items[i]);
	}
}

function refreshHUD(){
	//run to refresh the hud according to what's in items
	itemArr = [];
	for (var i=0;i<itemsHUD.length;++i){
	console.log("refresh hud: i : " + i);
		if (jQuery.inArray(itemsHUD[i], itemsEquip) > -1){
			//this item has been equiped, let's show it in the hud
			itemArr.push("<img src='/img/Assets/" + itemsHUD[i] + ".png'>");
		}else{
			//let's just show the shadow instead
			itemArr.push("<img src='/img/Assets/" + itemsHUD[i] + "-disabled.png'>");
		}
	}
	return itemArr.join("&nbsp;");
}



function sendMove(tileID, el){
	//don't send moves if you're dead silly
	if (hp <= 0){return false;}
	locked = true;
	data = mimicServer(tileID);
	if (data.accepted == true){
		console.log ("move accepted");
		//the move we sent was accepted
		el.className='clicked';
	    if (lastClicked) lastClicked.className='';
	    lastClicked = el;
	    lastTile = tileID;
	    //prevents the "start tile" from redrawing to a blank tile
	    if (tileID != startTile){lastClicked.innerHTML = "<img src='/" + getTileArt(data.tileType) + "'>";}
	    //check for items
	    if (data.items.length > 0){
		    //you got an item, let's handle it
		    console.log ("handling items in sendmove");
		    handleItems(data.items);
	    }
	    //alert chain
	    if (typeof data.alert != 'undefined') {
		    // the server sent back a custom message, let's display it
		    giveAlert(data.alert.type, data.alert.text, data.alert.dismissable);
		}else if (puzzleData.map[tileID] == 2){
			//win condition
			giveAlert("success", "Congratulations! You solved the puzzle successfully.  Click <a href='/tutorials'>HERE</a> to go back to a list of all the tutorials.", false);
			return;
		}
	    else if (data.hp <= 0){giveAlert("danger", "You hit a " + getTileName(data.tileType) + " tile and died! Much sad. :(<br>Click <a href='/try/" + GAME_ID + "'>HERE</a> to try this puzzle again or click <a href='/tutorials'>HERE</a> to go back to a list of all the tutorials.",false);}
	    else if (data.hp < hp && tileData.tiles[data.tileType].effect.hp < 0){giveAlert("warning", "You hit a " + getTileName(data.tileType) + " tile and took damage! (" + (hp - data.hp) + " hp)",true);}
	    else if (data.hp > hp && tileData.tiles[data.tileType].effect.hp > 0){giveAlert("info", "You were healed by a " + getTileName(data.tileType) + " tile and gained health! (" + (data.hp - hp) + " hp)",true);}
	    updatePlayer(data);
	}else{
		console.log ("move not accepted");
	}
	locked = false;
}

function keyMove(key){
	switch (key){
		case 37:
			//left
			tile = lastTile-1;
			break;
		case 38:
			//up
			tile = lastTile-puzzleData.dimensions.width;
			break;
		case 39:
			//right
			tile = lastTile+1;
			break;
		case 40:
			//down	
			tile = lastTile+puzzleData.dimensions.width;
			
	}
	if (validateClick(lastTile, tile, tile % puzzleData.dimensions.width)){
		console.log ("key validate passed");
		$('#gameGrid td:eq('+tile+')').trigger('click');
	}else{console.log("key validate failed");}
}


function clickableGrid( rows, cols, callback ){
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    grid.id = 'gameGrid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            //console.log("getTileArt: " + getTileArt(mapData[i]) + " - mapData: " + mapData[i] + " - i: " + i);
            cell.innerHTML = "<img src='/" + getTileArt(puzzleData.map[i]) + "'>";
            if (puzzleData.map[i] == 1){
            	//set starting position
            	cell.className='clicked';
            	lastClicked = cell;
            	lastTile = i;
            	startTile = i;
            }
            cell.addEventListener('click',(function(el,r,c,i){
                return function(){
                    callback(el,r,c,i);
                }
            })(cell,r,c,i),false);
            i++;
        }
    }
    return grid;
}

function validateClick(startTile, finishTile, finishTileColumn){
	console.log("You keyed to tile #:",startTile," from tile #: ",finishTile, " col: ",finishTileColumn, "locked: ", locked);
	if (locked == true){return false;}
	if (finishTile < 0 || finishTile >= (puzzleData.dimensions.height * puzzleData.dimensions.width)){return false;}
	if (startTile + 1 == finishTile && finishTileColumn != 0){
		//do left movement
		return true;
	}else if (startTile - 1 == finishTile && finishTileColumn != (puzzleData.dimensions.width - 1)){
		//do right movement
		return true;
	}else if (Math.abs(startTile - finishTile) == puzzleData.dimensions.width){
		//this is a top or bottom match
		return true;
	}
	return false;
}

function checkBlocking(i){
	//checks to make sure it's not blocking
	tileType = puzzleData.map[i];
	if (tileData.tiles[tileType].effect.blocking == true){
		if (typeof tileData.tiles[tileType].item != 'undefined'){
			if (typeof tileData.tiles[tileType].item.unblock != 'undefined'){
				//it can be unlocked, so let's see if it does
				if (jQuery.inArray(tileData.tiles[tileType].item.unblock, itemsEquip) > -1){
					return false;
				}else{
					return true;
				}
			}
		}
		return true;	
	}else{
		return false;
	}
}

function handleItems(items){
	//handles the items
	for (i = 0; i < items.length; ++i) {
		$.each(items[i], function( key, value ) {
			type = key.split("-");
			switch (type[0]){
				case "coin":
					//you got a coin
					lastClicked.innerHTML = "<img src='/img/Assets/" + key + ".png'>";
					giveAlert("info", "You found a " + type[1] + " coin worth " + value + " " + CURRENCY + "!",true);
					break;
				case "key":
					itemsEquip.push(key);
					giveAlert("info", "You found a " + type[1] + " key!",true);
					break;
			}
		});
	}
}

function giveAlert(type, text, dismissable){
	$("#alerts").prepend('<div class="alert alert-' + type + ' ' + ((dismissable == true)?'alert-dismissable':'') + '">' + ((dismissable == true)?'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>':'') + text + '</div>');
}

function getHearts(hp){
	f = Math.floor(hp / hearts);
	if (hp <= 0){
		//don't bother with h
		h = 0;
	}else{
		h = Math.floor((hp - (f * hearts)) / (hearts / 2));
	}
	i = 0;
	healthbar = "";
	while (i < f){
		//fill in full hearts
		healthbar += "<img src='/img/Assets/heart-full.png'>";
		i++;
	}
	if (h >= 1){
		//fill in half hearts
		healthbar += "<img src='/img/Assets/heart-half.png'>";
		i++;
	}
	while (i < hearts){
		//fill in empty hearts
		healthbar += "<img src='/img/Assets/heart-empty.png'>";
		i++;
	}
	return healthbar;
}

function getStatus(status){
	var statusArr = [];
	for (i = 0; i < status.length; ++i) {
    	statusArr.push(tileData.statuses[status[i]].desc);
	}
	if (statusArr.length == 0){
		//no status
		return "You're not suffering from any status effects!";
	}else{
		//you sick boy
		return statusArr.join("<br>");
	}
}

function getTileArt(id){
	//give me the id for the map, and i'll tell you what tile to use
	path = "img/Tiles/";
	id = parseInt(id);
	if (typeof tileData.tiles[id] == 'undefined'){
		path += "error.png";
	}else{
		path += tileData.tiles[id].file;
	}
	return path;
}

function getTileName(id){
	//give me the id for the map, and i'll tell you what tile to use
	id = parseInt(id);
	if (typeof tileData.tiles[id] == 'undefined'){
		name = "Error";
	}else{
		name = tileData.tiles[id].name;
	}
	return name;
}

function mimicServer(tileID){
	//this function mimics server responses
	//REMEMBER: don't modify values here (unless they are specifically server side), let the client update them!
	/*
	- accepted: true
	- hp: 75
	- items: []
	x sessionID: "586a5652827482dc"
	- status: []
	- tileID: "6"
	- tileType: 7
	*/
	data = {};
	//everything is an accepted move (probably). yay for the player! (but yay for my server even more)
	data.accepted = true;
	data.tileID = tileID;
	data.tileType = fullPuzzleData.map[tileID];
	data.items = [];
	data.status = serverStatus;
	data.hp = hp;
	//figure out items
	if (typeof tileData.tiles[data.tileType].item != 'undefined'){
		//there's an item here, let's see if it's equipable
		if (typeof tileData.tiles[data.tileType].item.equip != 'undefined'){
			//it is, so if it's not already in the array, let's push it
			if (jQuery.inArray(tileData.tiles[data.tileType].item.equip, itemsEquip) <= -1){
			console.log("found item, pushing");
				itemObject = {};
				itemObject[tileData.tiles[data.tileType].item.equip] = 1;
				data.items.push(itemObject);
			}
		}
	}
	//figure out statuses
	if (jQuery.inArray(data.tileID, serverMovechain) <= -1 || tileData.tiles[data.tileType].effect.rearm == true){
		//removal is first
		for (i = 0; i < serverStatus.length; ++i) {
			if (tileData.statuses[serverStatus[i]].remove == tileData.tiles[data.tileType].effect.status){
				data.status.splice(data.status.indexOf(tileData.tiles[data.tileType].effect.status), 1);
			}
		}
		serverStatus = data.status;
		//figure out HP based on which tileID they hit
		data.hp += tileData.tiles[data.tileType].effect.hp;
		//100 hp max cap
		if (data.hp > 100){data.hp = 100;}
		//tick status effects
		for (i = 0; i < serverStatus.length; ++i) {
			data.hp += tileData.statuses[serverStatus[i]].effect;
		}
		//apply status effects
		if (tileData.tiles[data.tileType].effect.status != "none" && jQuery.inArray(tileData.tiles[data.tileType].effect.status, serverStatus) <= -1){
			data.status.push(tileData.tiles[data.tileType].effect.status);
			serverStatus = data.status;
		}
	}else{
		for (i = 0; i < serverStatus.length; ++i) {
			data.hp += tileData.statuses[serverStatus[i]].effect;
		}
	}
	//update movechain
	serverMovechain.push(tileID);
	//show this trap over and over
	puzzleData.map[tileID] = data.tileType;
	return data;
}