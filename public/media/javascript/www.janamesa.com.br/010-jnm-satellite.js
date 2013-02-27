$(document).ready( function () {
    if ($('.cep_review') && $('.cep_review').length>0){
        $('.cep_review').html(ydRecurring.read()._lastarea);
        ''
    }
    $(".clear_cep").click(function() {
        ydRecurring.setLastArea('');
        ydRecurring.setLastOrder('');
        $('.cep_review').html(ydRecurring.read()._lastarea);
        $.Storage.remove('order-' + services[0].id + '-' + services[0].type );
        ydOrder.clear_bucket();
        ydOrder.update_view();    
        $.cookie('yd-state', null);
        alert($.cookie('yd-state'));
        ydMenuTrigger.init()
        
    });
    
    $('#yd-select-plz-form').submit(function () {
        
        var plzIsSet = ydMenuTrigger.isPlzSet(function(){
            alert("entrou");
        });
        if (!plzIsSet) {
            return false;
        }
        
        
        if (!this.plz.value.length) {
            notification("error", "Por favor informe o CEP onde a comida deverá ser entregue.");
            return false;
        }
        
        var cityId = $(this).data('city');
        var href = $(this).data('href');
        
        if (typeof ydState !== "undefined") {
            if (cityId !== undefined) {
                ydState.setCity(cityId);
            }
        }
        
        if (href !== undefined) {
            location.href = href;
            return false;
        }
    })
});

