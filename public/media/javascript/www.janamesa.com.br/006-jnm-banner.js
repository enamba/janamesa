(function($){
    var defaults = {
        speed: 200,
        delay: 4000,
        stopOnMouseOver: false,
        navigation: true
    };

    var methods = {
        init : function(options) {
            return this.each(function() {
                var $this = $(this),
                    data = $this.data('slideshow');

                if(!data) {
                    var settings = $.extend({}, defaults, options);

                    $this.data('slideshow', {
                        'speed': settings.speed,
                        'delay': settings.delay,
                        'stopOnMouseOver': settings.stopOnMouseOver,
                        'navigation':settings.navigation,
                        'timeOut':'',
                        'isPlaying':false,
                        'automatic': true
                    });
                }

                $this.data('slideshow').timeOut = setTimeout( function() {
                    slideSwitch($this);
                }, $this.data('slideshow').delay);

                if($this.data('slideshow').stopOnMouseOver) {
                    $('.seletor').mouseover(function() {
                        clearTimeout($this.data('slideshow').timeOut);
                        $this.data('slideshow').automatic = false;
                    });
                    $('.seletor').mouseout(function() {
                        $this.data('slideshow').automatic = true;
                        $this.data('slideshow').timeOut = setTimeout( function() {
                            slideSwitch($this);
                        }, $this.data('slideshow').delay);
                    });
                }

                if($this.data('slideshow').navigation) {
                    var navigation = $('<ul class="navigation" />');
                    $this.find('.item').each(function(index, item) {
                        var active = (index) ? '' : 'active';
                        navigation.append('<li class="' + active + '"></li>');
                    });
                    navigation.find('li').click(function() {
                        var index = $(this).index();
                        if(!$this.data('slideshow').isPlaying) slideSwitch($this, index);
                    });
                    $this.append(navigation);
                }
            });
        },
        destroy : function( ) {
            return this.each(function() {
                var $this = $(this);
                clearTimeout($this.data('slideshow').timeOut);
                $(window).unbind('.slideshow');
                $this.removeData('slideshow');
            });
        }
    };

    $.fn.slideNext = function (){
        slideSwitch(this, '', 'next');
    }

    $.fn.slidePrevius = function (){
        slideSwitch(this, '', 'previus');
    }

    $.fn.slideDefine = function (index){
        slideSwitch(this, index);
    }

    function slideSwitch($this, index, step) {
        if ($this.data('slideshow').isPlaying == true){
            return true;
        }
        $this.data('slideshow').isPlaying = true;
        if ($this.data('slideshow').automatic == true){
            clearTimeout($this.data('slideshow').timeOut);
        }

        var $active = $this.find('.item.active'),
            $next;
        if ($active.length==0) $active = $this.find('.item:last');

        if($.isNumeric(index) && index != $active.index()) {
            $next = $this.find('.item:eq(' + index + ')');
        } else if (!step || step == 'next'){
            $next = $active.next('.item').length ? $active.next('.item') : $this.find('.item:first');
        } else {
            $next = $active.prev('.item').length ? $active.prev('.item') : $this.find('.item:last');
        }

        $active.addClass('last-active');

        if ($this.data('slideshow').automatic == true){
            $next.css({opacity: 0})
                .addClass('active')
                .animate({opacity: 1}, $this.data('slideshow').speed, function() {
                    $active.removeClass('active last-active');
                    $this.data('slideshow').isPlaying = false;
                        $this.data('slideshow').timeOut = setTimeout( function() {
                             slideSwitch($this);
                    }, $this.data('slideshow').delay);
                });
        } else {
            $next.css({opacity: 0})
                .addClass('active')
                .animate({opacity: 1}, $this.data('slideshow').speed, function() {
                    $active.removeClass('active last-active');
                    $this.data('slideshow').isPlaying = false;
                });
        }

        $this.find('.navigation li').removeClass('active');
        $this.find('.navigation li:eq(' + $next.index() + ')').addClass('active');
    };

    $.fn.slideshow = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' + method + ' does not exist on jQuery.slideshow' );
        }
    };

})(jQuery);