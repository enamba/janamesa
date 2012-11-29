$(document).ready(function(){

    $(".yd-coupon-explanation").live('click', function(){
        openDialog('/request/coupon', {
            minHeight:900,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
        _gaq.push(['_trackPageview', '/lightbox/coupon-explanation']);
        return false;
    });

});