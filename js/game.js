var lastClicked;
var lastTile;

var puzzleData;

var hp = 100;

$( document ).ready(function() {
    console.log("DOM loaded");
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
	});
});

function sendMove(tileID, el){
	//don't send moves if you're dead silly
	if (hp <= 0){return false;}
	$.getJSON( "game.php?api=true&command=move&id="+GAME_ID+"&tileID="+tileID+"&sessionID="+sessionID, function( data ) {
		if (data.accepted == true){
			console.log ("move accepted");
			//the move we sent was accepted
			el.className='clicked';
		    if (lastClicked) lastClicked.className='';
		    lastClicked = el;
		    lastTile = tileID;
		    sessionID = data.sessionID;
		    lastClicked.innerHTML = "<img src='" + getTileArt(data.tileType) + "'>";
		    if (puzzleData.map[tileID] == 2){giveAlert("success", "Congratulations! You solved the puzzle successfully.  The reward amount for this puzzle has been deposited into your account", false);}
		    else if (data.hp <= 0){giveAlert("danger", "You hit a " + getTileName(data.tileType) + " tile and died! Much sad. :(",false);}
		    else if (data.hp < hp){giveAlert("warning", "You hit a " + getTileName(data.tileType) + " tile and took damage!",true);}
		    hp = data.hp;
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
	$("#game").append('<div class="alert alert-' + type + ' ' + ((dismissable == true)?'alert-dismissable':'') + '">' + ((dismissable == true)?'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>':'') + text + '</div>');
}

function getTileArt(id){
	//give me the id for the map, and i'll tell you what tile to use
	path = "img/Tiles/";
	id = id.toString();
	switch(id){
		case "0":
			path += "blank.png";
			break;
		case "1":
			path += "entrance.png";
			break;
		case "2":
			path += "exit.png";
			break;
		case "3":
			path += "lava.png";
			break;
		case "4":
			path += "mine.png";
			break;
		default:
			path += "error.png";
			break;
	}
	return path;
}

function getTileName(id){
	//give me the id for the map, and i'll tell you what tile to use
	id = id.toString();
	switch(id){
		case "0":
			name = "Tile";
			break;
		case "1":
			name = "Entrance";
			break;
		case "2":
			name = "Exit";
			break;
		case "3":
			name = "Lava";
			break;
		case "4":
			name = "Mine";
			break;
		default:
			name = "Error";
			break;
	}
	return name;
}