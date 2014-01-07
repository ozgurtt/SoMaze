var lastClicked;
var lastTile;
var startTile = null;

var tileData;
var puzzleData;

var hp = 100;

var leaving = true;

$("a").click(function()
{
   leaving = false;
});

window.onbeforeunload = function (e) {
  e = e || window.event;
  if (hp > 0 || leaving == false){return;}
  // For IE and Firefox prior to version 4
  if (e) {
    e.returnValue = 'If you refresh this page, you will be charged for entering the game again.  Are you sure you want to do this?';
  }
  // For others
  return 'If you refresh this page, you will be charged for entering the game again.  Are you sure you want to do this';
};

$( document ).ready(function() {
    console.log("DOM loaded");
    $.getJSON( "game.php?api=true&command=getTiles&id="+GAME_ID, function( data ) {
		//we got the tile, now get the game
		tileData = data;
		$.getJSON( "game.php?api=true&command=getMap&id="+GAME_ID, function( data ) {
			puzzleData = data;
			//console.log(mapData);
			var grid = clickableGrid(puzzleData.dimensions.height,puzzleData.dimensions.width,function(el,row,col,i){
			    console.log("You clicked on item #:",i);
				if (validateClick(lastTile, i, col)){
					console.log ("validate passed");
					sendMove(i, el);
				}else{console.log("validate failed");}
			});
			//document.body.appendChild(grid);
			$("#game").append(grid);
			$("#hp").html("<p>HP: " + data.hp + "</p>");
			hp = data.hp;
		});
	});
    
});

function sendMove(tileID, el){
	//don't send moves if you're dead silly
	if (hp <= 0){return false;}
	$.getJSON( "game.php?api=true&command=move&id="+GAME_ID+"&tileID="+tileID+"&sessionID="+sessionID, function( data ) {
		if (data.accepted == true){
			console.log ("move accepted (" + data.sessionID + ")");
			//the move we sent was accepted
			el.className='clicked';
		    if (lastClicked) lastClicked.className='';
		    lastClicked = el;
		    lastTile = tileID;
		    sessionID = data.sessionID;
		    //prevents the "start tile" from redrawing to a blank tile
		    if (tileID != startTile){lastClicked.innerHTML = "<img src='" + getTileArt(data.tileType) + "'>";}
		    if (puzzleData.map[tileID] == 2){giveAlert("success", "Congratulations! You solved the puzzle successfully.  The reward amount for this puzzle has been deposited into your account", false);}
		    else if (data.hp <= 0){giveAlert("danger", "You hit a " + getTileName(data.tileType) + " tile and died! Much sad. :(",false);}
		    else if (data.hp < hp){giveAlert("warning", "You hit a " + getTileName(data.tileType) + " tile and took damage!",true);}
		    hp = data.hp;
		    $("#hp").html("<p>HP: " + ((hp <0)?0:hp) + "</p>");
		}else{
			console.log ("move not accepted");
		}
	});
}


function clickableGrid( rows, cols, callback ){
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            //console.log("getTileArt: " + getTileArt(mapData[i]) + " - mapData: " + mapData[i] + " - i: " + i);
            cell.innerHTML = "<img src='" + getTileArt(puzzleData.map[i]) + "'>";
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

function validateClick(startTile, finishTile, startTileColumn){
	if (finishTile < 0 || finishTile >= (puzzleData.dimensions.height * puzzleData.dimensions.width)){return false;}
	if (startTile + 1 == finishTile && startTileColumn != 0){
		//do left movement
		return true;
	}else if (startTile - 1 == finishTile && startTileColumn != (puzzleData.dimensions.width - 1)){
		//do right movement
		return true;
	}else if (Math.abs(startTile - finishTile) == puzzleData.dimensions.width){
		//this is a top or bottom match
		return true;
	}
	return false;
}

function giveAlert(type, text, dismissable){
	$("#alerts").prepend('<div class="alert alert-' + type + ' ' + ((dismissable == true)?'alert-dismissable':'') + '">' + ((dismissable == true)?'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>':'') + text + '</div>');
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