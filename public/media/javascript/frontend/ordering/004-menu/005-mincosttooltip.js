$(document).ready(function(){
    var mincosttooltip = $('#yd-sidebar .yd-finish-order');
    mincosttooltip.mousemove(function(e){
            var offset = mincosttooltip.offset();
            var mincostx = e.pageX - offset.left;
            var mincosty = e.pageY - offset.top;
            $('.mincosttooltip').css({
                "top" : mincosty+20+"px",
                "left" : mincostx+20+"px"
            });
    });
});