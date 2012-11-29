/**
 * Lang
 * @author vpriem
 * @since 07.09.2011
 */
(function($) {
    $.lang = function(tag) {
        var msg = $("#yd-lang-" + tag).html() || "";
        
        if (arguments.length > 1) {
            for (var i = 1; i < arguments.length; i++) {
              msg = msg.replace(/(%s|%d)/, arguments[i]);
           }
        }

        return msg;
    };
    
    $.nlang = function(tag1, tag2, n) {
        var msg1 = $("#yd-lang-" + tag1).html() || "";
        var msg2 = $("#yd-lang-" + tag2).html() || "";
        
        var msg = msg1;
        if (n > 1) {
            msg = msg2;
        }

        if (arguments.length > 3) {
            for (var i = 3; i < arguments.length; i++) {
              msg = msg.replace(/(%s|%d)/, arguments[i]);
           }
        }

        return msg;
    };
})(jQuery);
