$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 25.06.2012
     */
    $(":checkbox.yd-checkbox").click(function(e){
        e.stopPropagation();
    })
    .closest("tr")
    .click(function(){
        var $chk = $(":checkbox.yd-checkbox", this);
        $chk.prop('checked', !$chk.is(':checked')).trigger("change.color");

    });
  
    /**
     * @author vpriem
     * @since 27.06.2012
     */
    $(":checkbox.yd-checkbox").bind("change.color", function(){
        var $tr = $(this).closest("tr");
        if (!$tr.length) {
            return;
        }
        if (this.checked) {
            $tr.removeClass("yd-grid-row-unseleted")
               .addClass("yd-grid-row-seleted");
        }
        else {
            $tr.removeClass("yd-grid-row-seleted")
               .addClass("yd-grid-row-unseleted");
        }
    });
    
    /**
     * @author vpriem
     * @since 27.06.2012
     */
    $('.yd-check-all-checkboxes').live('click', function(){
        $('.yd-check-all-checkboxes').prop('checked', this.checked);
        $('.yd-checkbox').prop('checked', this.checked)
                         .trigger("change.color");
    });
    
    /**
     * @author Alex Vait
     * @since 28.06.2012
     */
    $('.yd-check-all-checkboxes-2').live('click', function(){
        $('.yd-check-all-checkboxes-2').prop('checked', this.checked);
        $('.yd-checkbox-2').prop('checked', this.checked);
    });
    
    /**
     * @author Daniel Hahn
     * @since 09.07.2012
     */
    $('small#yd-order-grid-search').click(function(){
        window.location.href = "/administration_order/gridsearch";
        return false;
    });
    
});
