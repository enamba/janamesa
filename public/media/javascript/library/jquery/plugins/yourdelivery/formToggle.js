/**
 * Form Toggle
 * @author vpriem
 * @since 10.06.2011
 */
(function($) {
    $.fn.formToggle = function() {
        return this.each(function(){

            var toggeled = $(this).data('formToggle');

            if (toggeled === undefined) {
                toggeled = false;
            }
            
            if (toggeled === false) {
                $(':input:disabled, :input[readonly="readonly"]', this)
                    .data('wasDisabled', true);
                
                $(":text, :password, textarea", this)
                    .attr("readonly", "readonly")
                    .addClass("yd-form-toggled");
                
                $(":radio, :checkbox, :file, select", this)
                    .attr("disabled", "disabled")
                    .addClass("yd-form-toggled");
                    
                $(":button, :submit, :reset", this)
                    .css({visibility: "hidden"});
            }
            else {
                $(":text, :password, textarea", this).each(function(){
                   var $this = $(this).removeClass("yd-form-toggled");
                   
                   if ($this.data('wasDisabled') === true) {
                       $this.removeData("wasDisabled");
                   }
                   else {
                       $this.removeAttr("readonly");
                   }
                });
                
                $(":radio, :checkbox, :file, select", this).each(function(){
                   var $this = $(this).removeClass("yd-form-toggled");
                   
                   if ($this.data('wasDisabled') === true) {
                       $this.removeData("wasDisabled");
                   }
                   else {
                       $this.removeAttr("disabled");
                   }
                });
                
                $(":button, :submit, :reset", this)
                    .css({visibility: "visible"});
            }
            
            $(this).data('formToggle', !toggeled);
        });
    };
})(jQuery);