/*menu handler*/
$(function(){
  function getType(str) {
  	if(str.indexOf("make") >= 0) {
	  	return "create";
  	}
  	if(str.indexOf("game") >= 0) {
	  	return "play";
  	}
    if(str.indexOf("/") >= 0) {
    	//if we're not, process everything else like normal
		substr = str.substr(str.indexOf("/")+1, str.length);
		if (substr.indexOf("/") >= 0){
			//there's a second slash, let's get everything before it
			return substr.substr(0, substr.indexOf("/"));
		}else{
			return substr;
		}
    }else{
	    return "Home";
    }
  }
 
  var url = window.location.pathname;  
  var activePage = getType(url);

  $('.nav li a').each(function(){  
    var currentPage = getType($(this).attr('href'));
    console.log ("active page: " + activePage + " - currentpage: " + currentPage);
    if (activePage == currentPage) {
      $(this).parent().addClass('active'); 
    } 
  });
});