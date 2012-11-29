/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 15.06.2012
 */
$(document).ready(function() {
    
    
    $('#yd-service-faxservice-change').live('change', function(){
         var serviceId = $(this).attr('data-service-id');     
         var faxService = $(this).val();
         log("Setting Fax Service to:"+  $(this).val());
         
         $.ajax({
        cache: true,
        url: '/request_administration_service/faxservice',
        data: {
            serviceId : serviceId,
            faxService: faxService
        },
        success: function(response){
            
            if(response == 'OK') {
                notification('info', "Faxservice für DL " + serviceId + " wurde umgestellt auf " + faxService);
            }else{
                notification('info', "Faxservice konnte nicht umgestellt werden");
            }
            
             $('.yd-grid-box-close').trigger('click');
            
            if($('.ticketsystem').length > 0 && $('#yd-service-faxservice-change').length > 0) {
                $('#yd-service-faxservice-change').parents('.yd-grid').children('.yd-grid-trigger').text(faxService);
            }
            
            
        },
        error: function(){
            notification('info', "Faxservice konnte nicht umgestellt werden");
        }
    });
         
    });
    
});

