/**
 * @author vpriem
 * @since 21.06.2012
 */
$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 21.06.2012
     */
    $(".yd-blacklist-delete-value").live('click', function(){
        var $this = $(this);
        
        $.ajax({
            url: this.href,
            success: function(json){
                if (json.success) {
                    $this.closest("tr")
                         .find(".striked-0, .striked-1")
                         .removeClass("striked-0")
                         .removeClass("striked-1")
                         .addClass("striked-" + (json.deleted ? 1 : 0));
                    notification('success', json.success);
                } else if (json.error) {
                    notification('error', json.error);
                }
            },
            dataType: "json"
        }); 
        
        this.blur();
        return false;
    });
    
});