/**
 * crm objects autocomplete
 * @author alex
 * @since 20.06.2011
 */
(function($) {
    
    $.extend($.ui.autocomplete, {
        filter: function(array, term) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
            return $.grep( array, function(value) {
                return matcher.test( value.label || value.value || value );
            });
        }
    });    
    
    $.fn.bautocomplete = function (url, callback) {
        
        return this.each(function(){
            if (this.type !== 'text') return;

            var input = this;
            $(input).attr("autocomplete", "off");
            
            $.ajax({
                url: url,
                success: function (data) {
                    $(input).autocomplete({
                        source: data,
                        delay: 200,
                        minLength: 2,           
                        focus: function (event, ui) {
                            return false;
                        },
                        select: function (event, ui) {
                            $(input)
                            .val(ui.item.value)
                            .prev().val(ui.item.id);
                            
                            if (callback !== undefined) {
                                callback(ui.item);
                            }
                            
                            return false;
                        }
                    });
                },
                dataType: "json"
            });
            
        });
    };
})(jQuery);