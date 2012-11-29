$(document).ready(function(){

    /**
     * Forgott passwort
     * @author vpriem
     * @since 09.02.2011
     */
    $(".yd-forgotten-pass").live('click', function(){
        openDialog('/request/newpass', {
            width: 600,
            height: 400,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        }, function(){
            $("form", this)
            .ajaxForm({
                dataType: "json",
                success: function (json) {
                    if (json.success) {
                        notification("success", json.success);
                        closeDialog();
                    }
                    else if (json.error) {
                        notification("error", json.error);
                    }
                }
            })
            .find(":text:first").focus();
        });

        return false;
    });

});