$(document).ready(function(){
    $('#yd-kind-switch').live('change',function(){
        var form = $('#finishForm');
        $('input[name="finish"]').remove();
        if ( this.value == 'comp' ){
            form.attr('action','/order_private/finish');
        }
        else{
            form.attr('action','/order_private/finish');
        }
        form.validationEngine('detach');
        form.submit();
    });
});