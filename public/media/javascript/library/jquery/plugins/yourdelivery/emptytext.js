/**
 * Emptytext
 * @author vpriem
 * @since 23.03.2011
 */
(function($) {
    $.fn.emptytext = function() {
        return this.each(function(){

            var type = this.type.toLowerCase();
            if (type !== 'text' && type !== 'password' && type !== 'textarea') return;

            var emptytext = this.title;
            this.title = "";

            if (!emptytext.length) return;
                
            if ($(this.form).data("emptytext") !== true) {
                $(this.form).data("emptytext", true)
                            .submit(function(){
                                $(".yd-empty-text", this).trigger("click.emptytext");
                            });
                    
                $(":submit", this.form).click(function(){
                    $(".yd-empty-text", this.form).trigger("click.emptytext");
                });
            }

            if (!this.value.length) {
                this.value = emptytext;
            }

            $(this)
                .bind("click.emptytext keydown.emptytext", function(){
                    if (this.value == emptytext) {
                        $(this).removeClass("yd-empty-text")
                               .val("");
                    }
                })
                .bind("blur.emptytext", function(){
                    if (!this.value.length || this.value == emptytext) {
                        $(this).addClass("yd-empty-text")
                               .val(emptytext);
                    }
                });
        });
    };
})(jQuery);
