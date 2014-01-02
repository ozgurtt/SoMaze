/*menu handler*/
$(function(){
  function getType(str) {
  	if (str.indexOf("game.php") >= 0){
  		//traps if we're in a game, highlight "Play"
	    return "games";
    }
    if(str.indexOf("=") >= 0) {
    	//if we're not, process everything else like normal
		return str.substr(str.indexOf("=")+1, str.length);
    }else{
	    return "Home";
    }
  }
 
  var url = window.location.href;  
  var activePage = getType(url);

  $('.nav li a').each(function(){  
    var currentPage = getType($(this).attr('href'));
    console.log ("active page: " + activePage + " - currentpage: " + currentPage);
    if (activePage == currentPage) {
      $(this).parent().addClass('active'); 
    } 
  });
});