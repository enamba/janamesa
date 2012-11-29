
function customerinfo($box, $container){
                      
    //get data
    var customerId = $(this).attr('data-customerId');
    var email = $(this).attr('data-email');     
    var name = $(this).attr('data-customer-name');     
    var orderId = $(this).attr('data-orderId');     
    log('getting customer infobox for ' + customerId + '/' + email + '(' + name + ')');
            
    $.ajax({
        cache: true,
        url: '/administration_request_grid_customer/infobox',
        data: {
            customerId: customerId,
            email: email,
            name: name,
            orderId: orderId
        },
        success: function(html){ 
            $container.html(html);
            $box.show();
        }
    });
}

function emailinfo($box, $container){
    
    var email = $(this).attr('data-email');
    var orderId = $(this).attr('data-orderId');
    
    log('getting emaikl infobox for ' + email);
    
    $.ajax({
        cache: true,
        url: '/administration_request_grid_customer/emailinfo',
        data: {
            email: email,
            orderId: orderId
        },
        success: function(html){
            $container.html(html);
            $box.show();
        }
    });
}

$(document).ready(function(){
    
    /**
     *
     * Newsletter checkboxen für User Grid
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 07.06.2012
     */
    var newsletterXhr = null;
    $(':checkbox.yd-checkbox-newsletter').live("click", function(){        
        var checked = $(this).prop('checked');
        var email = $(this).attr('data-email');
        
        if (newsletterXhr !== null) {
            newsletterXhr.abort();
        }
        
        newsletterXhr = $.ajax({
            url: '/administration_request_grid_customer/tooglenewsletter',
            data: {
                email: email,
                status: checked
            },
            success: function(){
                $('.yd-checkbox-newsletter').each(function(){
                    if ( $(this).attr('data-email') == email ){
                        $(this).attr('checked',checked);
                    } 
                });
            }
        });
    });
});
   
