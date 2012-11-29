/**
 * show fidelity register lightbox on success
 * @author vpriem
 * @since 21.04.2011
 */
$(document).ready(function(){

    $('.yd-register-fidelity').live('click', function(){
        var id = this.id.split('-')[3];

        openDialog('/request_user/registerfidelity/id/' + id, {
            width: 600,
            height: 400,
            modal : true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        }, function(){

            if ($("form.yd-validation").length) {
                $("form.yd-validation").validationEngine({
                    promptPosition: "topRight",
                    validationEventTrigger: "blur",
                    success: false,
                    scroll: false
                });
            }

            $("form", this)
            .ajaxForm({
                dataType: "json",
                success: function (json) {
                    if (json.success) {
                        notification("success", json.success);
                        closeDialog();
                        setTimeout(function(){
                            //hack for gebelseiten.lieferando.de
                            if (typeof useNoHistoryBack !== 'undefined' && useNoHistoryBack) {
                                return false;
                            }
                            location.href = '/';
                        }, 300);
                    }
                    else if (json.error) {
                        notification("error", json.error);
                    }
                }
            })
            .find(":password:first").focus();
        });

        return false;
    });

});