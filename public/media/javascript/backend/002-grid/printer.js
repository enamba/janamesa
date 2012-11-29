/**
 * show the states history for this printer
 * 
 * @author Alex Vait
 * @since 30.08.2012
 */
function printerstateshistory($box, $container){

    // get data
    var printerId = $(this).attr('data-printerId');
    log('getting printer infobox for printer ' + printerId);

    $.ajax({
        cache: true,
        url: '/administration_request_grid_printer/infobox',
        data: {
            'printerId': printerId
        },
        success: function(html){ 
            $container.html(html);
            $box.show();
        }
    });
}
    
$(document).ready(function(){
    
    /**
     * set the firmware for selected printers
     * 
     * @author Alex Vait
     * @since 25.07.2012
     */
    $('#yd-save-new-printer-firmware').live('click',function(){
        var firmware = $('#yd-firmware-new-value').val();

        var notDigits = /^\s*\d+\s*$/;
        if (String(firmware).search (notDigits) == -1) {
            alert('Bitte geben Sie eine gültige Firmware ein!');
            return;
        }

        $('.yd-checkbox').each(function(){
            if ( $(this).is(':checked') ){
                var printerId = this.id.split('-')[3];
                $.post("/request_administration_service/setfirmware", {
                            'printerId' : printerId,
                            'firmware' : firmware
                }, 
                function(data) {
                    if (data) {
                        if (data.error) {
                            notification('error', data.message);
                        }
                        else if (data.success){
                            $('#yd-printer-upgrade-' + printerId).html(firmware);
                            notification('success', data.message);
                        }
                    }
                }, "json");                
            }            
        });
    });
});