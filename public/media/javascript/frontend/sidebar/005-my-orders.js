$(document).ready(function(){

    // do not expand favourits-sidebar
    if ($("#yd-expand-fav").length > 0) {
        $("#yd-expand-fav-minus").hide();
        $('#yd-expand-fav').hide();
        $("#yd-expand-fav-head").click(function() {
            $("#yd-expand-fav-plus, #yd-expand-fav-minus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

    // expand latest orders on start pages (sidebar)
    if ($("#expand-order").length > 0) {
        $("#expand-order").hide();
        $("#expand-order-minus").hide();
        $("#expand-order-head").click(function() {
            $("#expand-order-plus, #expand-order-minus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

});