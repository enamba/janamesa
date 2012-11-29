$(document).ready(function(){
    
    $('a.yd-rating-top-togglestatus').live('click', function(){
        var id = this.id.split('-')[4];
        $.post("/request_administration/toggleratingtop", {
            ratingId : id
        }, function (data) {
            var status = 0;
            if (data.state) {
                status = 1;
            }
            $('#yd-rating-top-togglestatus-' + id).html('<img src="/media/images/yd-backend/online_status_' + status + '.png"/ alt="Status ändern">');
        }, "json");
        this.blur();
        return true;
    });
    
    /**
     * @author vpriem
     * @since 11.04.2012
     */
    $(".yd-rating-sorry").live("click", function(){
        var url = this.href;
        var $this = $(this);
        
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
             prefix: 'promptf',
             loaded: function(){
                $('.promptfbuttons').hide();
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id', 'prompt-container');
                $('#prompt-container').load(url, function(){
                    
                     $(".yd-rating-sorry-select-call").mouseover(function(){
                         $(".yd-rating-sorry-call").hide();
                         $("#yd-rating-sorry-select").val(this.name);
                         $("#yd-rating-sorry-call-" + this.name).show();
                         
                     });
                    
                    $("form", this).ajaxForm({
                        beforeSubmit : function(arr, $form, options){
                            $(":submit", $form).hide();
                        },
                        success: function (data, status, xhr, $form) {
                            $(":submit", $form).show();

                            if (data.success) {
                                $.prompt.close();
                                $this.css("text-decoration", "line-through");
                                notification('success', data.success);
                            }
                            else if (data.error) {
                                $("#yd-rating-sorry-notify").html(data.error);
                            }
                        },
                        error: function() {
                            $(":submit", $form).show();
                        },
                        dataType: 'json'
                    });
                });
            }
        });

        return false; 
    });
    
    /**
     * @author vpriem
     * @since 11.05.2012
     */
    $("#yd-rating-sorry-prename").live("keyup", function(){
        $("span.yd-rating-sorry-prename").html(this.value);
    });
});