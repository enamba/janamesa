/**
 * send password to the partner restaurants or change email/mobile phone number
 * @author Alex Vait <vait@lieferando.de>
 * @since 01.08.2012
 */

function changeData(restaurantId, kind, newvalue, callback) {
    $.post("/request_administration_service/changepartnerdata", {
        'restaurantId' : restaurantId,
        'kind' : kind,
        'newvalue' : newvalue
    }, 
    function(data) {
        if (data) {
            notification(data.type, data.message);
            if (data.type == 'success') {
                $('#yd-send-password-' + kind + '-value').html(data.setvalue);
                $('#yd-password-page-' + kind).val(data.setvalue);
                
                if (jQuery.isFunction(callback)) {
                    callback(restaurantId, kind);                    
                }
            }
        }
    }, "json");
}

function sendPassword(restaurantId, kind) {
    
    if (!confirm($.lang('confirm-send-password')))
        return;
    
    $.post("/request_administration_service/sendpassword", {
        'restaurantId' : restaurantId,
        'kind' : kind
    }, 
    function(data) {
        if (data) {
            notification(data.type, data.message);
        }
    }, "json");   
}

$(document).ready(function(){
    
    /**
     * send password to the partner restaurants
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     */
    $('.yd-send-password').live('click', function() {       
        var restaurantId = $('#yd-password-page-restaurant-id').val();
        var kind = $(this).attr('id').split('-')[3];
        
        sendPassword(restaurantId, kind);
    });


    /**
     * change partner data
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     */
    $('.yd-password-page-change-data').live('click', function() {       
        var restaurantId = $('#yd-password-page-restaurant-id').val();
        var kind = $(this).attr('id').split('-')[4];
        var newvalue = $('#yd-password-page-' + kind).val();
        
        changeData(restaurantId, kind, newvalue, 0);
    });


    /**
     * change partner data and send new password
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     */
    $('.yd-password-page-change-data-and-send-password').live('click', function() {       
        var restaurantId = $('#yd-password-page-restaurant-id').val();
        var kind = $(this).attr('id').split('-')[4];
        var newvalue = $('#yd-password-page-' + kind).val();
        
        changeData(restaurantId, kind, newvalue, sendPassword);        
    });    
});