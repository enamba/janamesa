/**
 * testing fax and email delivery
 * @author Matthias Laug <laug@lieferando.de>
 * @since 02.03.2012
 */

$(document).ready(function(){
    //send test fax
    $('#yd-send-testfax').live('click', function(){
        
        var input = $('#yd-create-restaurant-fax');
        var fax = input.val();
        var faxService = $('#yd-testfax-service').val();

        if ( fax.length == 0 ){
            input.css('border-color','red');
        }
        else{
            var $this = $(this);
            var buttonText = $this.val();
            $this.val('... working ...');
            $this.attr('disabled','disabled');

            input.css('border-color','black');
            
            $.ajax({
                type: "POST",
                url : '/request_administration_service/testfax',
                dataType : 'json',
                data: 
                {
                    'fax'    : fax,
                    'faxService'    : faxService
                },
                success: function(json){
                    alert(json.message);
                    $this.val(buttonText);
                    $this.removeAttr('disabled');
                }
            });
        }
    });
    
    $('#yd-send-testemail').live('click', function() {
       
        var input = $('input[name="email"]');
        var email = input.val();
        if ( email.length == 0 ){
            input.css('border-color','red');
        }
        else{
            
            var $this = $(this);
            var buttonText = $this.val();
            $this.val('... working ...');
            $this.attr('disabled','disabled');
            
            input.css('border-color','black');
            $.ajax({
                type : "POST",
                url : '/request_administration_service/testemail',
                dataType : 'json',
                data: {
                    'email'  : email
                },
                success: function(json){
                    alert(json.message);
                    $this.val(buttonText);
                    $this.removeAttr('disabled');
                }
            });
        }
            
    });
    
});