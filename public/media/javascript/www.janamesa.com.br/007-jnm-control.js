$(window).load( function () {
    $('.entrar').click(function(){
        showLoginModal();
    });
    $('.entrar').mouseenter(function(){
        showLoginModal();
    });
    $('.entrar_modal').mouseleave(function(){
        hideLoginModal()
    });

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

