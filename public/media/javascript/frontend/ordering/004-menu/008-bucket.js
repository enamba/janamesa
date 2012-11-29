$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 08.06.2012
     */
    $("#yd-clear-bucket").click(function(){
        
        ydOrder.clear_bucket();
        
        $(this).hide();
        return false;
    });
});