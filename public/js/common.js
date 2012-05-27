$(function() {
  $("button#language").click(function() {
    pos = $(this).position();
    width = $(this).outerWidth();
    height = $(this).outerHeight();
    menu = $("div.language div.menu");
    menu.css({
      position: "absolute",
      top: (pos.top + height - 5) + "px",
      left: pos.left + "px",
      width: (width - 23) + "px",
    });
    menu.slideDown(100);
  });
  
  if ("viewLoad" in self) {
    viewLoad();
  }
});

String.prototype.format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) { 
    return typeof args[number] != 'undefined'
      ? args[number]
      : match
    ;
  });
};
