$(document).ready(function(){

    $('#etage-select, #yd-finish-lift').live('change',function(){
        // IE will not do that :(
        
        if($('#yd-finish-lift').attr('checked')){
             $('.floor').val('lift');
        }else{
            $('.floor').val($('#etage-select').val());
        }
        
        $('.yd-open-amount').html(ydOrder.calculate_open_amount(true));
        $('.yd-full-amount').html(ydOrder.calculate_amount(true));
        $('input[name=deliverCost]').val(ydOrder.get_deliver_cost());
        ydOrder.update_view_deliver_cost();
   });

});