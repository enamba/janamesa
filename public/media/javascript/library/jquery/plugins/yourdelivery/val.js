/**
 * intVal / floatVal
 * @author vpriem
 * @since 13.09.2011
 */
(function($) {
    $.fn.intVal = function (value) {
        if (value === undefined) {
            return parseInt($(this).val());
        }
        
        return $(this).val(parseInt(value));
    };
    
    $.fn.floatVal = function (value) {
        if (value === undefined) {
            return parseFloat($(this).val());
        }
        
        return $(this).val(parseFloat(value));
    }; 
})(jQuery);
