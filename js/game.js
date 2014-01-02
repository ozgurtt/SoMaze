var lastClicked;
var startTile = 5;
var lastTile = startTile;

var totalRows = 10;
var totalCols = 10;

var grid = clickableGrid(totalRows,totalCols,function(el,row,col,i){
    console.log("You clicked on element:",el);
    console.log("You clicked on row:",row);
    console.log("You clicked on col:",col);
    console.log("You clicked on item #:",i);
	if (validateClick(lastTile, i, col)){
		console.log ("validate passed");
		el.className='clicked';
	    if (lastClicked) lastClicked.className='';
	    lastClicked = el;
	    lastTile = i;
	}else{console.log("validate failed");}
    
});

document.body.appendChild(grid);

function clickableGrid( rows, cols, callback ){
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            cell.innerHTML = "<img src='img/blank.png'>";
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