// squidfingers.com

function blurAnchors(){
  if(document.getElementsByTagName){
    var a = document.getElementsByTagName("a");
    for(var i = 0; i < a.length; i++){
      a[i].onfocus = function(){this.blur()};
    }
  }
}