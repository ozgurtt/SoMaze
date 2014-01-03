var lastClicked;
var startTile = 48;
var lastTile = startTile;

var totalRows = 10;
var totalCols = 5;

var mapData;

$( document ).ready(function() {
    console.log("DOM loaded");
    $.getJSON( "game.php?api=true&command=getMap&id="+GAME_ID, function( data ) {
		mapData = data.map;
		//console.log(mapData);
		var grid = clickableGrid(totalRows,totalCols,function(el,row,col,i){
		    console.log("You clicked on item #:",i);
		    if (i == 5){giveAlert("warning", "YOU DIED DAWG");}
			if (validateClick(lastTile, i, col)){
				console.log ("validate passed");
				el.className='clicked';
			    if (lastClicked) lastClicked.className='';
			    lastClicked = el;
			    lastTile = i;
			}else{console.log("validate failed");}
		});
		//document.body.appendChild(grid);
		$("#game").append(grid);
	});
});


function clickableGrid( rows, cols, callback ){
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            //console.log("getTileArt: " + getTileArt(mapData[i]) + " - mapData: " + mapData[i] + " - i: " + i);
            cell.innerHTML = "<img src='" + getTileArt(mapData[i]) + "'>";
            i++;
            if (startTile == i){
            	//set starting position
            	cell.className='clicked';
            	lastClicked = cell;
            }
            cell.addEventListener('click',(function(el,r,c,i){
                return function(){
                    callback(el,r,c,i);
                }
            })(cell,r,c,i),false);
        }
    }
    return grid;
}

function validateClick(startTile, finishTile, startTileColumn){
console.log("validateClick: startTile: " + startTile + " - finishTile: " + finishTile);
	if (finishTile < 0 || finishTile > (totalRows * totalCols)){return false;}
	if (startTile + 1 == finishTile && startTileColumn != 0){
		//do left movement
		return true;
	}else if (startTile - 1 == finishTile && startTileColumn != (totalCols - 1)){
		//do right movement
		return true;
	}else if (Math.abs(startTile - finishTile) == totalCols){
		//this is a top or bottom match
		return true;
	}
	return false;
}

function giveAlert(type, text){
	$("#game").append('<div class="alert alert-' + type + '">' + text + '</div>');
}

function getTileArt(id){
	//give me the id for the map, and i'll tell you what tile to use
	path = "img/Tiles/";
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