function slideSwitch() {
    var $active = $('#slideshow LI.active');

    if ( $active.length == 0 ) $active = $('#slideshow LI:last');

    var $next =  $active.next().length ? $active.next()
        : $('#slideshow LI:first');

    $active.addClass('last-active');

    $next.css({opacity: 0.0})
        .addClass('active')
        .animate({opacity: 1.0}, 1000, function() {
            $active.removeClass('active last-active');
        });
}

$(function() {
    setInterval( "slideSwitch()", 6000 );
});