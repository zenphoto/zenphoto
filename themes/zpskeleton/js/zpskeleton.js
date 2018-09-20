
$(document).ready(function(){

  // Dropdown
  // ===============================

  $("body").bind("click", function (e) {
    $('a.menu').parent("li").removeClass("open");
  });

  $("a.menu").click(function (e) {
    var $li = $(this).parent("li").toggleClass('open');
    return false;
  });
  
  $("#slideshowlink a").addClass("button info");
  $(".newsarticle a img").addClass("button");
  $("input[type='submit']").addClass("button");
  $("input[type='reset']").addClass("button");
  $("input[type='button']").addClass("button");
  $(".buttons button").addClass("button");
  $(".random-image li").addClass("two columns image imagegrid");
  $("#latest li").addClass("two columns image imagegrid");
  
  $('img.remove-attributes').each(function(){
        $(this).removeAttr('width')
        $(this).removeAttr('height');
    });
  
});
