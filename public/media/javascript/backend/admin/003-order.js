$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 12.06.2012
     */
    $('.yd-order-comment').live('click', function(){
        var id = this.id.split('-')[3];
        
        openDialog('/request_administration/ordercomments/id/' + id, {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
        
        return false;
    });
    
    $('#yd-paypal-account-details').live('click', function(){
        
        var orderId = $(this).attr('data-grid-orderId');
        
        openDialog('/request_administration_paypal/info/orderId/' + orderId , {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
    });
 
    
    $('#yd-paypal-account-discounts').live('click', function(){
        
        var payerId = $(this).attr('data-grid-payerId');
        
        openDialog('/request_administration_paypal/discounts/payerId/' + payerId , {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
    });
    
});