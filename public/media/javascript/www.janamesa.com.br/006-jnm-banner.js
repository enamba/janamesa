var qtdBanner = 3;
var activeBanner = 0;
$ab = null;
function slideSwitch(setActive){
    if (!$ab) {
        console.log('definiu');

        $ab = setInterval( "slideSwitch()", 7000 );
    }
    if (setActive){
        console.log('limpou');
        clearInterval($ab);
    }

    if (setActive){
        activeBanner = setActive;
    }
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
        activeBanner = 0;
    }

    if (setActive) {
        console.log('redefiniu');
        $ab = setInterval( "slideSwitch()", 7000 );
    }

}

function proximo(){
    activeBanner++;
    if (activeBanner > qtdBanner){
        activeBanner = 0;
    }
    slideSwitch(activeBanner);
}

function anterior(){
    activeBanner--;
    if (activeBanner < 1){
        activeBanner = qtdBanner;
    }
    slideSwitch(activeBanner);
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