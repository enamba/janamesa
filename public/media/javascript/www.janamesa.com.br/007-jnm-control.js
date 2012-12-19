$(document).ready( function () {
    $('.entrar').click(function(){
        showLoginModal();
    });
    $('.entrar').mouseenter(function(){
        showLoginModal();
    });
    $('.entrar_modal').mouseleave(function(){
        hideLoginModal()
    });

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

