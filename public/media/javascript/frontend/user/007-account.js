$(document).ready(function(){
    
    /**
     * validate the settings form of a customer
     * @author vpriem
     * @since 28.11.2011
     * @view /user/settings /user/index
     */
    $(".yd-profile-picture").each(function(){
        var container = this;
        $(".window", container).click(function(){
            $(".upload", container).click();
        });
    });

    
    if (!$('#yd-user-settings-form').length) {
        return;
    }
    
    /**
     * validate the settings form of a customer
     * @author mlaug
     * @since 10.11.2011
     * @view /user/settings
     */
    $('#yd-user-settings-form').validationEngine({
        promptPosition: "topRight",
        validationEventTriggers: "blur",
        success: false,
        scroll: false,
        failure : function(){
            $('span.yd-user-form-error').show();
        }
    });
   
   /**
    * @author Daniel Hahn <hahn@lieferando.de>
    * @since 25.11.2011
    */
    $('#yd-user-settings-form input[name="newsletter"]').change(function(){
        if ( !$(this).is(':checked') ){
            var choice = confirm($.lang('confirm-newsletter-delete'));
            if ( !choice ){
                $(this).prop('checked',true);
                return false;
            }                        
        }
        
        var result = $(this).is(':checked') ? "1": "0";
        
        $.ajax({
            url: '/request_user/newsletter/newsletter/' + result ,                
            success: function (data) {
                
                notification("success",data ,false);
            }
        });
    });
   
   /**
    * @author Jens Naie <name@lieferando.de>
    * @since 12.07.2012
    */
    $('#yd-user-settings-form input[name="facebookId"]').change(function(){
        if ( !$(this).is(':checked') ){
            var choice = confirm($.lang('confirm-facebook-id-delete'));
            if ( !choice ){
                $(this).prop('checked',true);
            } else {
                $('#yd-user-settings-form').submit();
            }
        }
    });
   
   /**
    * @author Jens Naie <name@lieferando.de>
    * @since 12.07.2012
    */
    $('#yd-user-settings-form input[name="facebookPost"]').change(function(){
        if ( !$(this).is(':checked') ){
            var choice = confirm($.lang('confirm-facebook-post-delete'));
            if ( !choice ){
                $(this).prop('checked',true);
            } else {
                $('#yd-user-settings-form').submit();
            }
        } else {
            $('#yd-user-settings-form').submit();
        }
    });
   
   
});