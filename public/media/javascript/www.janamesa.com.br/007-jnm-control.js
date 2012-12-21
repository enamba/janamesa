$(document).ready( function () {
    exec = false;
    $('.entrar').click(function(){
        showLoginModal();
    });
    $('.entrar').mouseenter(function(){
        showLoginModal();
    });
    $('.entrar_modal').mouseleave(function(){
        hideLoginModal()
    });

    $(document).ready(function() {
        $('#slideshow').slideshow({
            'speed':1000, // Fading effect speed
            'delay':4000, // Delay between slides
            'stopOnMouseOver': true, //Stop sliding when mouse is over
            'navigation': true // Add navigation
        });
    });
    if (!$.browser.msie) {
        if ($(window).height() < 673) {
            $('.footerBanner').css('top', $(window).height() - 23);
        }
    }

//    $('.banner').mouseenter(function () {
//        $('.seletor').css({opacity: 0.0})
//            .animate({opacity: 1.0}, 1000, function() {
//                $('.seletor').css({opacity: 1.0})
//            });
//    });

//    $('.banner').mouseleave(function () {
//        $('.seletor').css({opacity: 1.0})
//            .animate({opacity: 0.0}, 1000, function() {
//                $('.seletor').css({opacity: 0.0})
//            });
//    });

});
//window.ready(alert('ready'));

$(window).scroll(function () {
    if (!$.browser.msie) {
        position = $(window).scrollTop() + $(window).height() - 23;
        if (position < 560){
            $('.footerBanner').css('top', position);
            $('.mainContent').css('margin-top', position - 673 );
        } else if ($(window).height() <= 570 ){
            $('.footerBanner').css('top', 560);
            $('.mainContent').css('margin-top', -117 );
        } else if ($(window).height() < 673 ){
            $('.footerBanner').css('top', $(window).height()-23);
            $('.mainContent').css('margin-top', $(window).height() - 698 );
        }
    }
});



$(window).resize(function() {
    if (!$.browser.msie) {
        $(window).scrollTop(0);
        if ($(window).height() < 673) {
            position = $(window).scrollTop() + $(window).height() - 23;
            $('.footerBanner').css('top', $(window).height() - 23);
            $('.mainContent').css('margin-top', position - 675 );
        } else {
            $('.footerBanner').css('top', 675);
            $('.mainContent').css('margin-top', 0 );
        }
    }
});


function showLoginModal () {
    $('.entrar_modal').show();
    $('.entrar').css('color', '#000');
}

function hideLoginModal() {
    $('.entrar_modal').hide();
    $('.entrar').css('color', '#fff');
}

function categorySetImage(target, imageName){
  target.src = '/media/images/jnm-frontend/category/' + imageName;
}