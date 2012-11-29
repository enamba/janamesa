/**
 * Emptytext
 * @author vpriem
 * @since 14.04.2011
 */
(function($) {
    $.fn.confirmation = function() {
        return this.each(function(){

            var text = this.title;
            this.title = "";

            if (!text.length) {
                text = "Are you sure?";
            }

            $(this).click(function(){
                return confirm(text);
            });

        });
    };
})(jQuery);