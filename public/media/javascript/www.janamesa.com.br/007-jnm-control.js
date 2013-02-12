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

    $('#slideshow').slideshow({
        'speed':1000, // Fading effect speed
        'delay':4000, // Delay between slides
        'stopOnMouseOver': true, //Stop sliding when mouse is over
        'navigation': true // Add navigation
    });

    if ($(window).height() < 673) {
        position = $(window).scrollTop() + $(window).height() - 23;
        if (position < 560){
            $('.header').css('height', position +23 );
            $('.banner').css('height', position +23 );
        } else if ($(window).height() <= 570 ){
            $('.header').css('height', 583 );
            $('.banner').css('height', 583 );
        } else if ($(window).height() < 673 ){
            $('.header').css('height', $(window).height() );
            $('.banner').css('height', $(window).height() );
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
    position = $(window).scrollTop() + $(window).height() - 23;
    if (position < 560){
        $('.header').css('height', position +23 );
        $('.banner').css('height', position +23 );
    } else if ($(window).height() <= 570 ){
        $('.header').css('height', 583 );
        $('.banner').css('height', 583 );
    } else if ($(window).height() < 673 ){
        $('.header').css('height', $(window).height() );
        $('.banner').css('height', $(window).height() );
    }
});


$(window).resize(function() {
    $(window).scrollTop(0);
    if ($(window).height() < 673) {
        position = $(window).scrollTop() + $(window).height() - 23;
        $('.header').css('height', $(window).height() );
        $('.banner').css('height', $(window).height() );
    } else {
        $('.header').css('height', 675 );
        $('.banner').css('height', 675 );
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

$(window).load(function(){
    $('.finish-button-limit').waypoint(function(direction) {
        if (direction == 'down'){
            $('#static_menu').fadeTo('slow', 0.8 , function() {
                $('#static_menu').css("display", "block");
            })
        } else {
            $('#static_menu').fadeTo('slow', 0, function() {
                $('#static_menu').css("display", "none");    
            })
        }
    });
});

$(document).ready(function(){
 if ($(".yd-full-amount")) {
     $("#jnm-full-amount-value").html($(".yd-full-amount").html());
 }
 
})