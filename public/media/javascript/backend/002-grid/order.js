
function orderoptions($box, $container){
    
    //get data
    var orderId = $(this).attr('data-order-id');     
    log('getting order infobox for ' + orderId);    
            
    $.ajax({
        cache: true,
        url: '/administration_request_grid_order/options',
        data: {
            orderId: orderId
        },
        success: function(html){
            $container.html(html);
            $box.show();
        }
    });
}