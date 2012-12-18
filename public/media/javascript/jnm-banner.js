var qtdBanner = 3;
var activeBanner = 2;

function slideSwitch(setActive){
    if (setActive){
        activeBanner = setActive;
    }
    var $selectorActive = $('.seletor LI.active');
    var $selectorNext = $('.seletor LI:nth-child(' + activeBanner + ')');
    $selectorActive.removeClass('active');
    $selectorNext.addClass('active')

    var $active = $('#slideshow LI.active');
    var $next =  $('#slideshow LI:nth-child(' + activeBanner + ')');

    $active.addClass('last-active');

    $next.css({opacity: 0.0})
        .addClass('active')
        .animate({opacity: 1.0}, 1000, function() {
            $active.removeClass('active last-active');
        });
    activeBanner++;
    if (activeBanner > qtdBanner){
        activeBanner = 1;
    }
}

function startCiclo() {
    return setInterval( "slideSwitch()", 7000 );
}

function stopCiclo(){
    clearInterval(ciclo);
}

function resetCiclo() {
    clearInterval(ciclo);
    return startCiclo();
}

var ciclo = startCiclo();

function slideTo(number){
    slideSwitch(number);
}

function selectSeletor(number){

}