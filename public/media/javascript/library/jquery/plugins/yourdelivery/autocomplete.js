/**
 * Plz autocomplete
 * @author vpriem
 * @since 16.03.2011
 */

(function($) {
    
    $.fn.plzAutocomplete = function (submit) {
        
        return this.each(function(){
            if (this.type !== 'text') return;

            var input = this;
            $(input).attr("autocomplete", "off");
            
            var extraValues = {};
            var classListString = $(input).attr('class');
            var valuesRegExp = /yd-autocomplete-value\[(\w+)\[(\w+)\]\]/g;
            var foundValue;
            while ((foundValue = valuesRegExp.exec(classListString))) {
                extraValues[foundValue[1]] = foundValue[2];
            }
            
            var cache = {}, lastXhr = null;
            
            $(input).keyup(function(event){
                // remove cityId on change
                if (event.which != 13) {
                    $(this).prev()
                        .val("");
                    $(this.form)
                        .removeData('href')
                        .removeData('city');
                }
            })
            .autocomplete({
                delay: 200,
                minLength: 2,
                focus: function (event, ui) {
                    return false;
                },
                source: function(request, response) {
                    $.extend(request, extraValues);
                    
                    var term = request.term;
                    if (term in cache) {
                        if(cache[term].length == 0) {
                            $(input).addClass('yd-form-invalid');
                        } else {
                            $(input).removeClass('yd-form-invalid');
                        }
                        log('loading data from cache for plz ' + term);
                        response(cache[term]);
                        return;
                    }

                    if (lastXhr !== null) {
                        lastXhr.abort();
                    }
                    $(input).addClass('waiting');
                    lastXhr = $.getJSON("/autocomplete/plz", request, function(data, status, xhr) {
                        $(input).removeClass('waiting');
                        if(!data || data.length == 0) {
                            $(input).addClass('yd-form-invalid');
                        } else {
                            $(input).removeClass('yd-form-invalid');
                        }
                        log('get data for plz ' + term);
                        cache[term] = data;
                        if (xhr === lastXhr) {
                            response(data);
                        }
                    });
                },
                open: function(event, ui) {
                    var term = input.value;
                    if (term in cache) {
                        if (cache[term].length == 1) {
                            var item = cache[term][0];
                            $(input.form)
                                .data('href', "/" + item.restUrl)
                                .data('city', item.id);
                                
                            $(input).autocomplete("close")
                                .val(item.value)
                                .prev()
                                .val(item.id);
                        }
                    }
                },
                select: function (event, ui) {
                    $(input.form)
                        .data('href', "/" + ui.item.restUrl)
                        .data('city', ui.item.id);

                    $(input).autocomplete("close")
                        .val(ui.item.value)
                        .prev()
                        .val(ui.item.id);
                        
                    ydRecurring.setLastArea(ui.item.plz);

                    if (submit === true || $(input).hasClass("yd-plz-autocomplete-autosubmit")) {
                        $(input.form).submit();
                    }
                    return false;
                }
            });
        });
    };
})(jQuery);
