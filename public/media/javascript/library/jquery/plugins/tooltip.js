(function($){
    $.fn.tooltip = function(){
        return this.each(function() {
            var text = $(this).attr("title");
            $(this).attr("title", "");
            if(text != undefined) {
                $(this).hover(function(e){
                    var tipX = e.pageX + 12;
                    var tipY = e.pageY + 12;
                    $(this).attr("title", "");
                    $("body").append("<div id='tooltip' style='position: absolute; z-index: 100; display: none;'>" + text + "</div>");
                    var tipWidth = null;
                    if($.browser.msie){
                        tipWidth = $("#tooltip").outerWidth(true);
                    }
                    else{
                        tipWidth = $("#tooltip").width();
                    }
                    $("#tooltip").width(tipWidth);
                    $("#tooltip").css("left", tipX).css("top", tipY).fadeIn("medium");
                }, function(){
                    $("#tooltip").remove();
                    $(this).attr("title", text);
                });
                $(this).mousemove(function(e){
                    var tipX = e.pageX + 12;
                    var tipY = e.pageY + 12;
                    var tipWidth = $("#tooltip").outerWidth(true);
                    var tipHeight = $("#tooltip").outerHeight(true);
                    if(tipX + tipWidth > $(window).scrollLeft() + $(window).width()){
                        tipX = e.pageX - tipWidth;
                    }
                    if($(window).height()+$(window).scrollTop() < tipY + tipHeight){
                        tipY = e.pageY - tipHeight;
                    }
                    $("#tooltip").css("left", tipX).css("top", tipY).fadeIn("medium");
                });
            }
        });
    };
})(jQuery);