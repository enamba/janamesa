$(document).ready( function () {
    if ($('.cep_review') && $('.cep_review').length>0){
        $('.cep_review').html(ydRecurring.read()._lastarea);
        ''
    }
    $(".clear_cep").click(function() {
        alert("Handler for .click() called.");
    });
});
