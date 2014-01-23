var tileData;
var puzzleData;

var selectedTile = 0;
var locked = false;

var lastClicked;


$( document ).ready(function() {
    console.log("DOM loaded");
    $("#metaform").hide();
    $.getJSON( "/api/v1/all/getTiles/", function( data ) {
		//we got the tile, now get the game
		tileData = data;
		$.getJSON( "/api/v1/creator/getMap/"+WIDTH+"/"+HEIGHT, function( data ) {
			puzzleData = data;
			var grid = clickableGrid(puzzleData.dimensions.height,puzzleData.dimensions.width,function(el,row,col,i){
				if (locked == true){return;}
			    console.log("You clicked on item #:",i);
			    puzzleData.map[i] = selectedTile;
				el.innerHTML = "<img src='" + getTileArt(selectedTile) + "'>";
			    
			});
			//document.body.appendChild(grid);
			$("#game").append(grid);
			$("#game").on('dragstart', function(event) { event.preventDefault();});
			var tiles = clickableTiles(10,10,function(el,i){
			    console.log("You clicked on tile #:",i);
				el.className='clicked';
				if (lastClicked) lastClicked.className='';
				lastClicked = el;
				$("#tileinfo").html(getTileInfo(i));
				selectedTile = i;
			});
			//document.body.appendChild(grid);
			$("#tiles").append(tiles);
			$("#tiles").on('dragstart', function(event) { event.preventDefault();});
			$("#nextstep").on("click", nextStep);
		});
	});
    
});

function nextStep(){
	//the map is done, submit it, get a cost, and fill in meta data
	console.log("sending puzzle data: ");
	console.log(puzzleData);
	$.ajax({
	  url: "/api/v1/creator/evalMap",
	  type: "POST",
	  data: JSON.stringify(puzzleData),
	  contentType: "application/JSON",
	  dataType: "json",
	  success:function(reply) {
	  	console.log("success: ");  
		console.log(reply);
		if (reply.valid == true){
			locked = true;
			$("#tiles").hide();
			$("#nextstep").hide();
			//populate the fee form
			$("#creationfee").html(reply.fee);
			$("#entry").attr({
				"min" : reply.fee	
			})
			$("#reward").attr({
				"min" : reply.fee	
			})
			$("#metaform").show();

		}else{
			giveAlert("danger", "You must have exactly one entrance and one exit in your puzzle", true);
		}
	  },
	  error:function(e) {
		console.log("error: ");  
		console.log(e);
	  }
	});

}

function clickableGrid( rows, cols, callback ){
console.log("drawing grid");
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            //console.log("getTileArt: " + getTileArt(mapData[i]) + " - mapData: " + mapData[i] + " - i: " + i);
            cell.innerHTML = "<img src='" + getTileArt(puzzleData.map[i]) + "'>";
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

function clickableTiles( rows, cols, callback ){
console.log("drawing tiles");
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'tilegrid';  
    for (var r=0;r<rows;++r){
    	if (i >= tileData.tiles.length){break;}
	    var tr = grid.appendChild(document.createElement('tr'));
	    for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            cell.innerHTML = "<img src='" + getTileArt(i) + "'>";
            cell.addEventListener('click',(function(el,i){
                return function(){
                    callback(el,i);
                }
            })(cell,i),false);
            i++;
            if (i >= tileData.tiles.length){break;}
        }
    }  
    return grid;
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

function getTileInfo(id){
	//give me the id for the map, and i'll tell you what tile to use
	id = parseInt(id);
	if (typeof tileData.tiles[id] == 'undefined'){
		content = "Error";
	}else{
		content = "<p>" + tileData.tiles[id].name + " - Cost: " + tileData.tiles[id].cost.DOGE + "<br>" + tileData.tiles[id].desc + "<br>Damage: " + tileData.tiles[id].effect.hp + "hp - Rearm: " + tileData.tiles[id].effect.rearm + " - Status Effect: " + tileData.tiles[id].effect.status + "</p>";
	}
	return content;
}