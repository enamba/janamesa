$(document).ready( function () {
    if (typeof ydABTestGroup != 'undefined') {
        if (ydABTestGroup == 'B') {
            $('INPUT[name="BUSCAR RESTAURANTES"]').val("FAZER MEU PEDIDO");
            $('INPUT[name="BUSCAR RESTAURANTES"]').css("padding-left", 0);
        } else {
            $('INPUT[name="BUSCAR RESTAURANTES"]').val("BUSCAR RESTAURANTES");

        }
    }
})
