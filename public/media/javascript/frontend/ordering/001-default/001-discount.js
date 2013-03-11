
function filloutDiscount(code, minAmount, kind, info){ 
    // fill html
    $('.yd-valid-discount-code').html(code);
    $('.yd-valid-discount-minamount').html(minAmount);
    $('.yd-valid-discount-kind').html(kind);
    $('.yd-valid-discount-info').html(info);
}

/**
 * toggle delivertime when discount is added
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 28.08.2011
 */
function hideDelivertimeSelect(){
    $('ul.yd-deliver-time-input').hide();
    $('#yd-finish-deliver-time option:selected').val($('#deliver-time-now').val());
}

function showDelivertimeSelect(){
    $('#yd-finish-deliver-time option:selected').val($('#yd-finish-deliver-time option:selected').html());
    $('ul.yd-deliver-time-input').show();
}

/**
 * use the bar payment if the voucher covers the entire bucket amount
 * @author Matthias Laug <laug@lieferando.de>
 */
function useBarPaymentIfVoucherCoversAmount(){  
    var openAmount = ydOrder.calculate_open_amount(false);
                
    // if open amount is 0, deactivate all payment kinds, but activate bar payment
    if (parseInt(openAmount) == 0) {
        $('.yd-finish-payment').removeClass('active');
        $('.yd-finish-payment-radio').prop("checked", false)
            .removeClass("validate[payment]");
    }
    else {
        $('.yd-finish-payment-radio').addClass("validate[payment]");
    }
}

/**
 * show info for discount
 * @author Matthias Laug <laug@lieferando.de>
 */
function showDiscountInfo(){
    $('.yd-discount-info').show();
}
function hideDiscountInfo(){
    $('.yd-discount-info').hide();
}

/**
 * validate discount and add to html
 * @author mlaug
 * @since 01.09.2011
 */
function checkDiscount(){
    var code = $("#discount-code").val();
    if (!code.length) {
        return false;
    }
    
    $('#check-discount').addClass('discount-load');
    
    $.ajax({
        url: '/request_order/adddiscount/',
        data: {
            code: code,
            service: $('input:[name="serviceId"]').val(),
            customer: false,
            kind: $('#kind').val(), //isn't this obsolete and replacable with ydState.getKind();
            city: ydState.getCity()
        },
        dataType: "json",
        success: function (json) {
            $('#check-discount').removeClass('discount-load');
            $('#yd-discount-add').hide();
            $('#yd-discount-content').html(json.html);
                
            if (json.result == false) {
                $('.valid-discount, .valid-user-discount').hide();
                $('.invalid-discount').show();
                $('.yd-discount-msg').html(json.msg);
                $('input[name=discount]').val('');
                showDelivertimeSelect();
                return false;
            }
            else{
                var discount = new YdDiscount();
                discount.kind = json.data.kind;
                discount.value = parseInt(json.data.value);
                discount.min_amount = parseInt(json.data.minAmount);
                ydOrder.add_discount(discount);
                //check for the min amount and mark to user
                if (!ydOrder.is_minamount_reached()) {
                    ydOrder.remove_discount();
                    log('discount minamount of '+discount.min_amount + ' not reached');
                    $('.valid-discount, .valid-user-discount').hide();
                    $('input[name=discount]').val('');
                    $('#yd-discount-add').show();
                    notification('error',json.data.minAmountMsg,true);
                    showDelivertimeSelect();
                    return false;
                }
         
                $('.valid-discount').show();
                $('.yd-cash-fidelity-points').show();
                
                filloutDiscount(code, json.data.minAmountHtml, json.data.kindHtml, json.data.info);
                $('input[name=discount]').val(code);
                log(json);
                if (!json.allowCash) {
                    hidePayment("bar");
                    hidePayment("ec");
                    hidePayment("vr");
                    hidePayment("creditathome");
                }
                if (!json.allowCredit) {
                    hidePayment("credit");    
                }
                if (!json.allowEbanking) {
                    hidePayment("ebanking");  
                }
                
                // hide delivertime select
                hideDelivertimeSelect();  
                
                //use bar payment, if voucher covers amount
                useBarPaymentIfVoucherCoversAmount();
                selectFirstVisiblePayment();
                
                //show infos
                showDiscountInfo();
                
                $('.yd-open-amount').html(ydOrder.calculate_open_amount(true));
            }            
        }
    });
}

$(document).ready(function(){

    /**
     * Add rabatt / discount on clicking "einlösen"
     */
    $('#check-discount').live('click', function(){
        checkDiscount();
        return false;
    });

    $('.yd-discount-info').live('click', function(){
        $(this).hide();
    }); 

    /**
     * Remove rabatt / discount
     * @author vpriem
     * @since 29.07.2011
     */
    $("a.yd-link-rabatt-delete").live('click', function(){   
        $('.yd-cash-fidelity-points').show();
        $('#yd-discount-add').show();
        $('.invalid-discount, .valid-discount, .valid-user-discount').hide();
        $('input[name="fidelity"]').val(0);
        $('input[name="discount"]').val('');
        $("#discount-code").val("");
        $('.yd-open-amount').html('');
        showDelivertimeSelect();
        ydOrder.remove_discount();
        
        var newCustomerWarning = $('.discount-warn');
        
        if (newCustomerWarning) {
            $(newCustomerWarning).hide();
        }
        selectFirstVisiblePayment();
        hideDiscountInfo();
        return false;
    });

});
