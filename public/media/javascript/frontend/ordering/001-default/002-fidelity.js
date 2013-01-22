$(document).ready(function(){

    var maxCost = null;

    /**
     * cash fidelity points
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.02.2011
     */
    function cashFidelityPoints() {
        //find highest rated meal
        var maxMeal = null;
        $.each(ydOrder.meals, function(k,v){
            log(v.name);
            if (v.getAllCost(false, true) <= maxCost && (maxMeal == null || v.getAllCost(false, true) > maxMeal.getAllCost(false, true)) ){
                maxMeal = v;
            }
        });

        //add text to discount content
        if ( maxMeal ){
            log('found a meal ('+maxMeal.name+') to use for fidelity points, adding as discount');

            //remove discount field
            $('#yd-discount-add').hide();

            //remove fidelity discount box
            $('.yd-cash-fidelity-points').hide();

            var fidelity = new YdDiscount();
            fidelity.kind = 1;
            fidelity.value = maxMeal.getAllCost(false, true);
            ydOrder.add_discount(fidelity);

            //mark this discount as an fidelity discount
            $('input[name="fidelity"]').val(1);

            var kind = $.lang('redeem-fidelity-kind');
            var info = $.lang('redeem-fidelity-info', maxMeal.name, int2price(maxMeal.getAllCost(false, true)));
            filloutDiscount('', '', kind, info);
            $('.valid-discount, .valid-user-discount').show();

            $('.yd-open-amount').html(ydOrder.calculate_open_amount(true));

            //remove cash if available
            hidePayment("bar");
            selectFirstVisiblePayment();

            hideDelivertimeSelect();

            useBarPaymentIfVoucherCoversAmount();

            //show infos
            showDiscountInfo();
        }
        else{
            log('found no meal, informing customer');
            notification('warn', $('#yd-fidelity-no-meal-found').val());
            showDelivertimeSelect();
        }
    }

    /**
     * Cash fidelity points AJAX wrapper
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 18.06.2012
     */
    $('a.yd-cash-fidelity-points').live('click', function () {
        if (maxCost !== null) {
            cashFidelityPoints();
        } else {
            $.ajax({
                url: '/request_order/getfidelitymaxcost/',
                dataType: "json",
                success: function (json) {
                    maxCost = parseInt(json.maxCost);
                    // check whether limit has been correctly retrieved from server side
                    if (isNaN(maxCost)) {
                        log('Fidelity maxCost value is not a number - silently giving up!');
                        // maybe next time error won't occur?
                        maxCost = null;
                        return ;
                    }
                    log('Fidelity maxCost: ' + maxCost);
                    cashFidelityPoints();
                },
                error: function () {
                    log('An error occured during maxCost value retrieving - silently giving up!');
                }
            });
        }
    });
});
