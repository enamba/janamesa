/**
 * @author vpriem
 * @since 14.06.2012
 */
$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 14.06.2012
     */
    $(".yd-blacklist-lightbox").live('click', function(){
        openDialog(this.href,{
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        }, function(){
            $('.yd-empty-text', this).emptytext();
            
            var $form = $('form',this).ajaxForm({
                dataType : 'json',
                beforeSend: function(){
                    $form.find(":submit")
                         .prop('disabled', "disabled")
                         .addClass("button-load");
                }, 
                complete: function(){
                    $form.find(":submit")
                         .removeProp('disabled')
                         .removeClass("button-load");
                },
                success: function(){
                    closeDialog();
                    return false;
                },
                error: function(xhr){
                    if (xhr.status == "406") {
                        $form.find('.form-error').remove();
                        var json = JSON.parse(xhr.responseText);
                        $form.find('input, textarea').each(function(){
                            var $this = $(this);
                            if (json[this.id]) {
                                $this.css('border-color', 'red');
                                $.each(json[this.id], function(k, v){
                                    $this.after('<p class="form-error">' + v + '</p>');
                                });
                            }
                        })
                        return false;
                    }
                }
            });
        });
        
        this.blur();
        return false;
    });
    
    /**
     * @author vpriem
     * @since 19.06.2012
     */
    $(".yd-blacklist-lightbox-matchings").live('click', function(){
        openDialog(this.href,{
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
        
    this.blur();
        return false;
    });
});