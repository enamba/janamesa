/**
 * @author vpriem
 * @since 08.02.2012
 */
function initServiceTypeFilter() {
    
    var filter = "rest";
    if (ydState.getKind() == 'comp') {
        filter = $.cookie("yd-filter-type") || filter;
    }

    var $checkboxes = $(":checkbox.yd-filter-service-type").each(function(){
        if (this.value == filter)  {
            if ($(this).attr('disabled')) {
                filter = "unknow";
            }
            else {
                this.checked = true;
                return false;
            }
        }
    });
    
    // get first available checkbox
    if (filter == "unknow") {
        $checkboxes.each(function(){
            if (!$(this).attr('disabled')) {
                this.checked = true;
                return false;
            }
        });
    }
    
    $checkboxes.each(function(){
        if (this.checked) {
            $("div.yd-service-type-" + this.value).show();
        } else {
            $("div.yd-service-type-" + this.value).hide();
        }
    });
}

/**
 * @author vpriem
 * @since 08.02.2012
 */
$(document).ready(function(){
   
    if (!$('.yd-service-page').length) {
        return;
    }
    
    var $checkboxes = $(":checkbox.yd-filter-service-type").change(function(){
        if (!this.checked) {
            this.checked = true;
        } else {
            $checkboxes.attr('checked', false);
            this.checked = true;
            $.cookie("yd-filter-type", this.value);
        }
        
        $checkboxes.each(function(){
            if (this.checked) {
                $("div.yd-service-type-" + this.value).show();
            } else {
                $("div.yd-service-type-" + this.value).hide();
            }
        });
        
    });
    
    initServiceTypeFilter();
   
});