/**
 * Show payment form
 * @author vpriem
 * @since 08.02.2011
 */
function showPaymentForm(payment) {
    
    $("div.yd-payment-form").hide();
    $("#yd-payment-form-" + payment).show();
}

/**
 * @author vpriem
 * @since 07.02.2012
 */
function selectFirstVisiblePayment() {
    
    $(':radio.yd-finish-payment-radio').prop("checked", false);
    $('div.yd-finish-payment').removeClass('active');
    
    $('.yd-finish-payment:visible').each(function(){
        var payment = this.id.split('-')[3];
        log('select first visible payment ' + payment);
        $(this).addClass('active')
            .find(":radio")
            .prop("checked", true);
            
        showPaymentForm(payment);
        
        return false;
    });
}

/**
 * @author vpriem
 * @since 07.02.2012
 */
function selectPayment(payment) {
    
    $(':radio.yd-finish-payment-radio').prop("checked", false);
    $('div.yd-finish-payment').removeClass('active');
    log('try to select payment ' + payment);
    $('#yd-finish-payment-' + payment)
        .addClass('active')
        .find(":radio")
        .prop("checked", true);
}

/**
 * @author vpriem
 * @since 07.02.2012
 */
function showPayment(payment) {
    
    $('#yd-finish-payment-' + payment).show();
}
    
/**
 * @author vpriem
 * @since 07.02.2012
 */
function hidePayment(payment) {
    
    $('#yd-finish-payment-' + payment).hide();
}

$(document).ready(function(){

    /**
     * Show selected payment form
     * @author vpriem
     * @since 08.02.2011
     */
    $(':radio:checked.yd-finish-payment-radio').each(function(){
        showPaymentForm(this.value);
    });

    /**
     * Select payment method
     * @author vpriem
     * @since 08.02.2011
     * @see http://ticket.yourdelivery.local/browse/YD-775
     */
    $('.yd-finish-payment').live('click', function(){

        var payment = this.id.split('-')[3];

        $(':radio.yd-finish-payment-radio').prop("checked", false);
        $(':radio.yd-payment-addition-radio').prop("checked", false);
        $('div.yd-finish-payment').removeClass('active');

        var $payment;
        if ($(this).hasClass("yd-payment-addition")) {
            $payment = $('#yd-finish-payment-bar');
            $(this).find(":radio")
                .prop("checked", true);
        }
        else {
            $payment = $(this);
        }
        
        $(this).addClass('active');
        $payment.find(":radio")
            .prop("checked", true);

        showPaymentForm(payment);

        return false;
    });
    
    /**
     * @author vpriem
     * @since 18.11.2011
     */
    $("#yd-save-cc")
    .live("click", function(){
        if (this.checked) {
            $(":radio.yd-creditcard").prop("checked", false);
        }
    })
    .closest("li").hover(function(){
        $("em", this).show();
    }, function(){
        $("em", this).hide();
    });
    
    $("input.yd-creditcard").live("click", function(){
        if (this.checked) {
            $("#yd-save-cc").prop("checked", false);
        }
    });

});