$(document).ready(function(){

    /**
     * Register for newsletter
     * @author vpriem
     * @since 14.02.3011
     */
    $("#yd-register-newsletter").ajaxForm({
        success: function (data) {
            if (data.success) {
                notification("success", data.success);
            }
            else if (data.error) {
                notification("error", data.error);
            }
        },
        dataType: "json"
    });
    
    /**
     * register for newsletter at not-online-yet domains
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.06.2011
     */
    $('#closed-europe-submit').live('click', function(){
        $(this).addClass('closed-europe-submit-loading');
        $('#closed-europe-result').html('');
        var email = $('#closed-europe-email').val();
        if (email == '' || email.length < 5) {
            return false;
        }
        $.ajax({
            url: '/request_user/registernewsletter/',
            data: {
                email: email
            },
            success: function (json) {
                $('#closed-europe-submit').removeClass('closed-europe-submit-loading');
                if (json.success) {
                    $('#closed-europe-content').hide();
                    $('#closed-europe-thx').show();
                }
                else{
                    $('#closed-europe-result').html(json.error);
                }
            },
            dataType: "json"
        });
    });
    
});