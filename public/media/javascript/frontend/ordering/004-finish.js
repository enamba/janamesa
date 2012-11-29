$(document).ready(function(){
    
    if (!ydState.maybeLoggedIn() && $('#finishForm').length) {
        $('#login').append('<input type="hidden" name="login_finish" value="1" />');
        $('#login').append('<input type="hidden" name="kind" value="' + ydState.getKind() + '" />');
    }
    
    /**
     * send out an private single order of a restaurant
     * here only deliver information must be checked
     * and payment
     */
    var hasBeenSubmited = false;

    // IE 6 gets no inline validation - only validation on click the butto
    // that effects that the layout will not be zerschossen
    if(!($.browser.msie && $.browser.version.split('.')[0] <= 7)){
        if ($('#finishForm').length) {
            $('#finishForm').validationEngine({
                promptPosition: "topRight",
                validationEventTriggers: "blur",
                success: false,
                scroll: true
            });
        }
    }
    
    $('.yd-finish-payment').click(function() {
        $('.formError').hide();
        $('#paymentDiv').css('border-width', '0');
    });
    
    
    $('#yd-private-single-finish, #yd-private-single-finish-bottom, #yd-company-single-finish, #yd-company-single-finish-bottom').live('click', function() {
            
        if (hasBeenSubmited === true) {
            return false;
        }
            
        $('.finish-button-top')
        .removeClass('button-2')
        .addClass('button-2-working');
        $('.finish-button')
        .removeClass('button-2')
        .addClass('button-2-working');
        $('#openingDiv').css('border', '1px solid #ccc');
        $('#yd-finish-deliver-time-day').css('border', '1px solid #555');
        if($('#finishForm').validationEngine({
            returnIsValid: true,
            onErrorShow: function (caller) {
                if($(caller).prop('name') == 'payment') {
                    if($("input[name='payment']:checked").size() == 0) {
                        $('#paymentDiv').css('border', '2px solid red');
                    }else{
                        $('#paymentDiv').css('border', 'none');
                    }                  
                }                              
            },
            onErrorClose: function(caller) {                
                if($(caller).prop('name') == 'payment') {
                    $('#paymentDiv').css('border', 'none');
                }
            }
        }) == true){
                
            if($('#yd-finish-deliver-time-notime').length > 0){
                $('#openingDiv').css('border', '2px solid red');
                $('#yd-finish-deliver-time-day').css('border', '2px solid red');
                notification('warn','Bitte überprüfen Sie die Lieferzeit.');
                setTimeout(function() {
                    $('.finish-button-top')
                    .removeClass('button-2-working')
                    .addClass('button-2');
                    $('.finish-button')
                    .removeClass('button-2')
                    .addClass('button-2');
                }, 1000);
                return false;
            }
        
            //show presuccess content
            $('#yd-finish-content').hide();
            $('#yd-finish-presuccess-content').show();
                    
            // fix für doppelte submits
            //     $('#yd-shopping-positions input').remove();
            $('#finishForm').submit();
            hasBeenSubmited = true;
            return false;
        }
            
        setTimeout(function() {
            $('.finish-button-top')
            .removeClass('button-2-working')
            .addClass('button-2');
            $('.finish-button')
            .removeClass('button-2-working')
            .addClass('button-2');
        }, 1000);
        
        return false;
    });

});
