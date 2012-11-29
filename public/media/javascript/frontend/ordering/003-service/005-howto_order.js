/**
 * howto Order Box arrow animation
 * @author tmeuschke
 * @since 05.10.2011
 */
function howtoOrder(i){
    if(i >= 3){
        i=0;
    }
    $('#howto-lightbox .arrow-gray').eq(i).animate({
        "borderLeftColor":"green"
    }, 300).delay(1000).animate({
        "borderLeftColor":"#999"
    }, 300, function() {
        i++;
        howtoOrder(i);
    });      
}

/**
 * howto Order Box stop animation
 * @author tmeuschke
 * @since 05.10.2011
 */
function howtoStopAnimation() {
    $('#howto-lightbox .arrow-gray').queue("fx", []);
    $('#howto-lightbox .arrow-gray').stop();
}

/**
 * howto Order Box more
 * @author tmeuschke
 * @since 05.10.2011
 */
function howtoMore(){
    $('#howto-lightbox .howto-more').bind('click', function(){
        $('#howto-lightbox .howto-more').hide();
        $('#howto-lightbox .howto-less').show();
        $('#howto-lightbox .howto-more-text').show();
    });
    $('#howto-lightbox .howto-less').bind('click', function(){
        $('#howto-lightbox .howto-less').hide();
        $('#howto-lightbox .howto-more').show(); 
        $('#howto-lightbox .howto-more-text').hide();
    });
}

$(document).ready(function(){
    
    /**
     * howto Order Box
     * @author tmeuschke
     * @since 05.10.2011
     */
    $("#yd-how-to-order").bind('click', function(){ 
        $(".howto-lightbox-overlay").show();
        $("#howto-lightbox").slideDown("fast", function(){
            $("#howto-lightbox-close").bind("click", function(){
                $("#howto-lightbox").slideUp("fast", function(){
                    $(".howto-lightbox-overlay").hide();
                    howtoStopAnimation();
                });
            });
            $(".howto-lightbox-overlay").bind('click', function(){ 
                $("#howto-lightbox").slideUp("fast", function(){
                    $(".howto-lightbox-overlay").hide();
                    howtoStopAnimation();
                });
            });
            //throw out IE - no animated arrows
            if(!$.browser.msie) {
                var i = 0;
                howtoOrder(i);
            }
        });
        howtoMore();
    });
});