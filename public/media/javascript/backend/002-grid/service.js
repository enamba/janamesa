function serviceoptions($box, $container){
    
    //get data
    var serviceId = $(this).attr('data-service-id');     
    log('getting service infobox for ' + serviceId);    
            
    $.ajax({
        cache: true,
        url: '/administration_request_grid_service/options',
        data: {
            serviceId : serviceId
        },
        success: function(html){
            $container.html(html);
            $box.show();
        }
    });
}


function faxServiceSelect($box, $container){
    
    var serviceId = $(this).attr('data-grid-service-id');    
    log('gettting fax infobx for ' + serviceId);
    
    $.ajax({
        cache: true,
        data: {
            serviceId : serviceId
        },
        url: '/administration_request_grid_service/fax',        
        success: function(html){
            $container.html(html);
            $box.show();
        }
    });
    
}