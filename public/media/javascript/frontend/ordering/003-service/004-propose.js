$(document).ready(function(){
    
    if ( $.browser.msie && $.browser.version < 9 ){
        return;
    }
    
    /**
     * @author mlaug
     * @since 01.09.2011
     */
    $('.yd-service-page').each(function(){
        ydRecurring.loadLastOrder();
        return false;
    });
    
});