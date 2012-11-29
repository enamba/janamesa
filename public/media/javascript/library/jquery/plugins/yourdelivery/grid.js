(function($) {

    $.fn.gridBox = function(){
        return this.each(function(){
            var $this = $(this);
            var $parent = $(this).parent(".yd-grid");
            
            var $box = $parent.find('.yd-grid-box');
            if (!$box.length) {
                $box = $('<div class="yd-grid-box"></div>').appendTo($parent);
            }
            
            if (!$(".yd-grid-box-close", $box).length) {
                $box.prepend('<a href="#" class="yd-grid-box-close"></a>');
            }
            if (!$(".yd-grid-box-content", $box).length) {
                $box.append('<ul class="yd-grid-box-content"><li class="yd-grid-box-loading"></li></ul>');
            }
            
            $this.live("click", function(){
                log('clicked a grid trigger');

                // hiding all other boxes
                $('.yd-grid-box').hide();
                
                // check if box already loaded
                if ($this.data('loaded')) {
                    log('box already loaded, showing former output');
                    $box.show();
                    return false;
                }

                $box.show();
                
                var callback = $this.attr('data-grid-callback');
                log('searching for callback ' + callback);
                try {
                    var fn = window[callback] || function ($b, $c) {
                        log('default callback triggered');
                        $b.show();
                    };
                    if (typeof fn === 'function') {
                        fn.call($this[0], $box, $("ul", $box));
                        $this.data('loaded', true);
                        return false;
                    }
                }
                catch(e) {
                    log('error while loading callback ' + callback + ': ' + e);
                }

                log('could not find or trigger callback ' + callback);
            });
        });
    };
})(jQuery);

$(document).ready(function(){
    $('.yd-grid-trigger').gridBox();
    
    $('.yd-grid-box-close').live("click", function(){
        $(this).parent().hide();
        return false;
    });
});
