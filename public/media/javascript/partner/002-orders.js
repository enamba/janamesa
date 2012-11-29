
$(document).ready( function() {
    
    /**
     * hightlight based on dash
     * @author mlaug
     * @since 17.11.2011
     */
    if ( $('#yd-order-grid').length > 0){
       if ( $('#yd-order-grid').length > 0 ){
        $('.status').each(function(){
          
            if ( $(this).html() == "Storno" ){
                $(this).parent().css('background','#ffcece');
            }
            
            if ( $(this).html() == "Bestätigt" ){
                $(this).parent().css('background','#d5ffce');
            }
            
        });
    }
    }
    
});