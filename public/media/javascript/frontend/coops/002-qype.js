$(document).ready(function(){
    
    $('#yd-plz-2').css('display','none');

    $('#yd-plz-1,#yd-plz-2').live('click',function(){
        $(this).parent().find('#yd-plz-1,#yd-plz-2').toggle();
    });
    
    //close the discount info field
    $('#yd-frame-discount-close, #yd-frame-discount-ok').live('click',function(){
        $('#yd-frame-discount').remove(); 
    });

    // search for new plz on press enter key
    $('#qype-search-plz-2').keyup(function(e){
        if(e.keyCode == 13) {
            var redirect = $('input[name=redirect_to]').val();
            var plz = $('#qype-search-plz-2').val();
            //redirect parent
            window.top.location.href = redirect + '?postcode=' + plz;
        }
    });
    
    $('#qype-search').live('click',function(){
        var redirect = $('input[name=redirect_to]').val();
        var plz = $('#qype-search-plz-2').val();
        //redirect parent 
        window.top.location.href = redirect + '?postcode=' + plz;
    });
    
    //hide blue boxes by default 
    $('.yd-frame-bluebox').css('display','none');

    //show blue box
    $('#yd-show-bluebox').live('click',function(){
        $(this).next().toggle();
    });
    
    //hide blue box again
    $('#yd-hide-bluebox').live('click',function(){
        $(this).parent().toggle();
    });

    //change the current plz of a selected service
    $('#qype-search-plz-1').live('change',function(){
        var cityId = $('#qype-search-plz-1').val();
        ydState.setCity(cityId);
        $('input[name=cityIdTmp]').val(cityId);
    });
    
    $('.yd-frame-payment-qype').live('click', function(event){
        if ( ydOrder.is_minamount_reached() ){ 
            event.stopPropagation();
            log('submitting form to parent');
            $('input[name=cityId]').val($('input[name=cityIdTmp]').val());
            $('input[name=cityIdTmp]').remove();
            $('#yd-finish-order-form').submit();
            log($('#yd-finish-order-form'));
          
            return false;
        }
        else{
            log('min amount has not been reached');
            // show info to user
            $('.min-amount-not-reached-info').fadeIn('fast');
            setTimeout(function(){
                $('.min-amount-not-reached-info').fadeOut('slow');
            }, 3000);
            return false;
        }
    });   
});

