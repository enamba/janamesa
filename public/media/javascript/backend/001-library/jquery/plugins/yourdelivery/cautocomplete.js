/**
 * crm objects autocomplete
 * @author alex
 * @since 20.06.2011
 */
(function($) {
    
    $.widget( "custom.citycomplete", $.ui.autocomplete, {
        _renderMenu: function( ul, items ) {
            var f=this;
            $.each(items,function(c,e){
                f._renderItem(ul,e)
            });           
        }
    });
    
    
    $.extend($.ui.autocomplete, {
        filter: function(array, term) {
            if(!isNaN(parseInt(term))){
                var matcher = new RegExp( "^"+ $.ui.autocomplete.escapeRegex(term), "i" );
                return $.grep( array, function(value) {
                    return matcher.test( value.label || value.value || value );
                });                
            }else {
                var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
                return $.grep( array, function(value) {
                    return matcher.test( value.label || value.value || value );
                });
            }
                                   
        }
    });    
    
    $.fn.cautocomplete = function (url, callback , openfunc) {
        
        return this.each(function(){
            if (this.type !== 'text') return;

            var input = this;
            $(input).attr("autocomplete", "off");
            
            $.ajax({
                url: url,
                success: function (data) {
                    $(input).citycomplete({
                        source: data,
                        delay: 200,
                        minLength: 2,           
                        focus: function (event, ui) {
                            return false;
                        },
                        select: function (event, ui) {
                            $(input)
                            .val(ui.item.value)
                            .prev()
                            .val(ui.item.id);
                            
                            if (callback !== undefined) {
                                callback(ui.item);
                            }
                            
                            return false;
                        },
                        open: function(event, ui) {                             
                            if(openfunc !== undefined) {
                                openfunc(event)
                            }                                                     
                        }                                              
                    });
                },
                dataType: "json"
            });
            
        });
    };
})(jQuery);