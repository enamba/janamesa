
/**
 * resizes the height for orders_grid (user-backend)
 * @author Allen Frank <frank@lieferando.de>
 */
function resizeHeight(size, diff){
    group = $(".yd-profile-nav,.yd-profile-body");
    
    group.each(function() {
        thisHeight = $(this).height();
        log('height:'+thisHeight+' size:'+size+' diff:'+diff+'=>erg: '+size*diff + ' ergo: ' + (thisHeight>(size * diff)));
        $(this).height((thisHeight>(size * diff)) ? (thisHeight - (size * diff)) : (thisHeight + (size * diff)));
    });
}

/**
 * Show a certain amount of orders
 * @author allen frank
 * @since 11-11-11
 */
function resizeShownOrders(oldLimit){
    var start = parseInt(oldLimit);
    var end = parseInt($('#limit').val());
    var rowSize = $('#user_orders tr').height();
    var thisHeight = $(".yd-profile-nav,.yd-profile-body").height();
    
    $(".yd-profile-nav,.yd-profile-body").height(thisHeight + (rowSize * (end-start)));  
    (start<end) ? $('#user_orders tr').slice(start+2, end+2).show() : $('#user_orders tr').slice(end+2, start+2).hide();
}
var count=0;
$(document).ready(function() {    
    
 
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 18.11.2011
     * orders grid colour storno orders
     */
    //color orders in grid
    if ( $('#user_orders').length > 0 ){
        $('.state').each(function(){
             if ( $(this).html().indexOf("Bestellung storniert") != -1 ){
                $(this).parent().css('background-color','#ffefef');
            }
        });
    }
    
    
    //take away pagination, because its to difficult to translate
    var lastTd= $('#user_orders table tr:last td');
    
    if(lastTd.prop('colspan') == 7) {
        lastTd.hide();
    }
    
    
    /**
     * show / hide full category name in menu on mouseover / -out
     * @todo: this can be done with toggle!
     * @author oknoblich
     * @since 10.10.2011
     */
    $('.mealname-lenght-toggle').live('mouseover', function(){
        $(this).children('.mealname-lenght-toggle-full').show();
    });
    $('.mealname-lenght-toggle').live('mouseout', function(){
        $(this).children('.mealname-lenght-toggle-full').hide();
    });
    
    
    /**
     * show plz request for the order now button if no address is saved
     * @author jens naie <naie@lieferando.de>
     * @since 20.08.2012
     */
    
    $('.yd-button-order-now').live('click', function() {
        if(!ydCustomer.hasLocations()) {
            openDialog('/request/postalrequest', {
            width: 600,
            height: 400,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        }, function(){
            if(typeof(LOCALE) != 'undefined' && LOCALE == 'pl_PL') {
                $('form.pl-autocomplete-city-street').cityStreetAutocomplete();
            } else {
                $('#yd-plz-search').plzAutocomplete(true);
                $('#yd-plz-search').focus();
            }
        });

        return false;        
        }
    });
    
    
});