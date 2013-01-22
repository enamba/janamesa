/**
 * @author mlaug
 * @since 08.11.2011
 */
function callMeal(mealId, sizeId, update){
    update = update || false;
    delete document.lastMealOptionCost;
    
    log('loading meal ' + mealId);
    openDialog('/request_order/callmeal/id/' + mealId + '/size/' + sizeId + '/update/' + update, {
        width: 600,
        height: 380,
        modal: true,
        close: function(e, ui) {
            $(ui).dialog('destroy');
        }
    });
    //track with piwik that this item has been added
    _paq.push(['setEcommerceView',mealId]);
    log('tracking view for ' + mealId);
}

/**
 * @author alex
 * @since 05.12.2011
 */
function updateMeal(hash){
    var meal = ydOrder.meals[hash];
    openDialog('/request_order/callmeal/id/' + meal.id + '/size/' + meal.size.id + '/update/' + 1, {
        width: 600,
        height: 380,
        modal: true,
        close: function(e, ui) {
            $(ui).dialog('destroy');
        }
    }, function (){
        var sumCost = meal.size.cost;
        var sizeName = encodeURIComponent(meal.size.name).replace(/%/g,'');
        var mealOptionCount = 0;

        meal.options.forEach(function (o) {
            if (o.type == 'mealoption') {
                $('.yd-option-item-' + o.id + '-' + sizeName + ':eq(' + mealOptionCount + ')').prop("checked", true);
                if (!document.lastMealOptionCost) {
                    document.lastMealOptionCost = new Array();
                }
                document.lastMealOptionCost[mealOptionCount] = o.cost;
                mealOptionCount++;
            } else {
                $('.yd-option-item-' + o.id).prop("checked", true);
            }
            sumCost = sumCost+o.cost;
        });
        $('.yd-current-extras .yd-dbh-choise').html();
        meal.extras.forEach(function (e) {
            sumCost = sumCost + (e.cost * e.count);
            for(var i=0;i<e.count;i++){
                addExtra($('#meal-extras-' + e.id + '-' + meal.size.id)[0]);
            }
        });

        $('.yd-update-to-card', this).prop('id', hash);
        $('#yd-meal-count', this).val(meal.count);
        $('.yd-meal-cost', this).val(sumCost);
        $('#yd-meal-cost-hidden', this).val(meal.size.cost);
        $('.yd-current-meal-price', this).html(int2price(sumCost));
        $('textarea[name="special"]', this).val(meal.special);
    });
}

/**
 * @author alex
 * @since 05.12.2011
 */
$(".yd-update-to-card").live("click", function(){
    var hash = this.id;
    ydOrder.remove_meal(hash);
    $('.yd-add-to-card').click();
});

/**
 * @author mlaug
 * @since 16.11.2011
 */
function addExtra(button){
    var extraId = button.id.split('-')[2];
    var sizeId = null;
    var form = button.form;

    $('.yd-change-size:checked', form).each(function(){
        sizeId = this.value;
        return;
    });

    if (sizeId === null) {
        sizeId = form.sizeId.value;
    }

    var sizeCost = $("#yd-meal-cost-base").intVal();
    var extraCost = parseInt(form["extras_" + extraId + "-" + sizeId + "_cost"].value);
    var countFormElem = form["extras_" + extraId + "-" + sizeId + "_count"];
    var nameFormElem = form["extras_" + extraId + "-" + sizeId + "_name"];
    var extraCount = parseInt(countFormElem.value);
    countFormElem.value = extraCount + 1;

    sizeCost = sizeCost + extraCost;

    $('.yd-current-extras .yd-dbh-extras').append('<a class="yd-extras-remove" data-extra-cost="'+extraCost+'" data-extra-size-id="'+sizeId+'" data-extra-id="'+extraId+'">' + nameFormElem.value + ((extraCost>0) ? ' (' + int2price(extraCost, true) + ')' : '') + '</a>');

    $("#yd-meal-cost-base").val(sizeCost);
    $(".yd-current-meal-price").html(int2price(sizeCost));
}

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 06.06.2012
 */
function removeExtra(a){
    var $a = $(a);
    var sizeId = $a.attr('data-extra-size-id');
    var extraId = $a.attr('data-extra-id');
    var cost = $a.attr('data-extra-cost');
    var sizeCost = $("#yd-meal-cost-base").intVal();
    sizeCost = sizeCost - cost;
    $("#yd-meal-cost-base").val(sizeCost);
    $(".yd-current-meal-price").html(int2price(sizeCost));
    var count = $('input[name="extras_' + extraId + '-' + sizeId + '_count"]');
    if ( count.val() > 0 ){
        count.val(count.val()-1);
    }
    $a.remove();
}

$(document).ready(function(){

    /**
     * trigger the selected meal from the search of service page
     * @author mlaug
     */
    var triggerSearch = getUrlVars()['mealid'];
    if (triggerSearch) {
        callMeal(triggerSearch);
    }

    /**
     * @author vpriem
     * @since 14.04.2011
     */
    $(".yd-add-to-card").live("click", function(){
        log('trying to add meal to cart');
        var form = this.form;
        var mealId = parseInt(form.mealId.value);
        var mealName = form.mealName.value;
        var exMinCost = parseInt(form.exMinCost.value) ? true : false;

        // check minAmount
        // TODO: inform user
        var count = parseInt(form.count.value);
        var minCount = parseInt(form.minCount.value);

        $('#eingabefeld').css('border', ''); // reset css
        if (count < minCount) {
            $('#eingabefeld').css('border-color', 'red');
            log("mincount isn't reached");
            $('#yd-error-mincount').show();
            return false;
        }

        // check options choices
        // TODO: inform user
        var optCorrect = true;
        $('.optrow', form).each(function(){

            var $this = $(this);

            var optId = this.id.split('-')[1];
            var choicesMin = parseInt($this.attr('data-min-choices'));
            var choicesMax = parseInt($this.attr('data-max-choices'));
            var count = $(':checkbox:checked', this).length + $(':radio:checked', this).length;

            $('#yd-so-many-choices-' + optId).css('color', ''); // reset css

            if (count < choicesMin || count > choicesMax) {
                $this.removeClass('yd-dialogs-green').addClass('warning');
                // scroll to the first unchecked option
                if (optCorrect) {
                    var $container = $(document);
                    if ($container.length){
                        $container
                        .scrollTop(0)
                        .scrollTop($this.offset().top);
                    }
                }
                optCorrect = false;
            }
            else{
                $this.addClass('yd-dialogs-green').removeClass('warning');
            }
        });
        if (!optCorrect) {
            $('div.yd-dialogs').addClass('warning');
            log('options are not correctly selected');
            return false;
        }
        else{
            $('div.yd-dialogs').removeClass('warning');
        }

        var ydMeal = new YdMeal();
        ydMeal.id = mealId;
        ydMeal.name = mealName;
        ydMeal.count = count;
        ydMeal.minCount = minCount;
        ydMeal.exMinCost = exMinCost;
        ydMeal.special = form.special.value;

        var ydSize = null;

        $('.yd-change-size:checked', form).each(function(){
            var sizeId = this.value;
            var name = form["sizes_" + sizeId + "_name"].value;
            var cost = form["sizes_" + sizeId + "_cost"].value;
            ydSize = new YdSize(sizeId, name, cost);
            return false; // one time
        });
        if (ydSize === null) {
            var sizeId = form.sizeId.value;
            var cost = form.sizeCost.value;
            ydSize = new YdSize(sizeId, "Normal", cost);
        }
        ydMeal.size = ydSize;

        if (document.lastMealOptionCost) {
            delete document.lastMealOptionCost;
        }
        var costMax = 0;
        $('.yd-check-option:checkbox:checked', form).each(function(){
            var optionId = this.value;
            var groupId = this.id.split("-")[1];
            var name = form["options_" + groupId + "_" + optionId + "_name"].value;
            var cost = form["options_" + groupId + "_" + optionId + "_cost"].value;
            var ydOption = new YdOption(optionId, name, cost);
            ydMeal.addOption(ydOption);
        });
        var ydSizeName = encodeURIComponent(ydSize.name).replace(/%/g,'');
        $('.yd-check-option:radio:checked', form).each(function(){
            var optionId = this.value;
            var groupId = this.id.split("-")[1];
            if (costMax < form["options_" + groupId + "_" + optionId + "_" + ydSizeName + "_cost"].value) {
                costMax = form["options_" + groupId + "_" + optionId + "_" + ydSizeName + "_cost"].value;
            }
        });
        $('.yd-check-option:radio:checked', form).each(function(){
            var optionId = this.value;
            var groupId = this.id.split("-")[1];
            var name = form["options_" + groupId + "_" + optionId + "_" + ydSizeName + "_name"].value;
            var cost = form["options_" + groupId + "_" + optionId + "_" + ydSizeName + "_cost"].value;
            if (form.priceType.value == "options_avg") {
                cost /=  parseInt($('#choices-' + groupId).attr('data-max-choices'));
            } else if(form.priceType.value == "options_max") {
                if (cost == costMax) {
                    costMax = -1;
                } else {
                    cost = 0;
                }
            }
            var ydMealOption = new YdMealOption(optionId, name, cost);
            ydMeal.addOption(ydMealOption);
        });

        $('.yd-extra-' + ydSize.id, form).each(function(){
            log(form);
            var extraId = this.value;
            var count = form["extras_" + extraId + "-" + ydSize.id +  "_count"].value;
            if ( count > 0 ){
                var name = form["extras_" + extraId + "-" + ydSize.id +  "_name"].value;
                var cost = form["extras_" + extraId + "-" + ydSize.id +  "_cost"].value;
                var ydExtra = new YdExtra(extraId, name, cost, count);
                ydMeal.addExtra(ydExtra);
            }
        });
        ydOrder.add_meal(ydMeal);

        closeDialog();
        if ($(this).hasClass("yd-iframe-add-to-card")) {
            $('.extras').hide();
            $('.activeiframe').removeClass('activeiframe');
            $('#yd-extras-' + mealId).hide("slow");
        }

        return false;
    });

    /**
     * add an meal element to our card
     * @author mlaug
     * @since 05.01.2011
     */
    $(".add-to-card").live("click", function(){

        var data = this.id.split('-');
        var mealId = data[1];
        var sizeId = data[2];
        var hasSpecials = data[3];

        var plzIsSet = ydMenuTrigger.isPlzSet(function(){
            callMeal(mealId, sizeId);
        });
        if (!plzIsSet) {
            return false;
        }

        /**
         * Slide meal into shopping card
         * excluding IE cause it sucks
         * @author vpriem
         * @since 27.06.2011
         */
        if (hasSpecials == "0" && !$.browser.msie) { // has to be a string
            log('adding meal directly, no options and extras available and size selected');

            var ydMeal = new YdMeal();
            ydMeal.id = mealId;
            ydMeal.name = $("#yd-mealName-" + mealId).val();
            ydMeal.count = 1;
            ydMeal.exMinCost = $("#yd-mealExMinCost-" + mealId).intVal() ? true : false;

            var sizeName = $("#yd-sizeName-" + sizeId + "-hidden").val();
            var cost = $("#yd-mealCost-" + mealId + "-" + sizeId).val();
            ydMeal.size = new YdSize(sizeId, sizeName, cost);

            var hash = ydOrder.add_meal(ydMeal, null, false);

            var fromOffset = $(this).offset();
            var targetOffset;
            if ($("#yd-meal-count-li-" + hash).length) {
                targetOffset = $("#yd-meal-count-li-" + hash).offset();
            }
            else {
                targetOffset = $("#yd-shopping-positions").offset();
                targetOffset.top += $("#yd-shopping-positions").height();
            }
            $('<ul class="yd-flying-meal"></ul>')
            .append(ydMeal.getHtml(hash))
            .offset(fromOffset)
            .appendTo("body")
            .animate({
                top: targetOffset.top,
                left: targetOffset.left
            }, 1000, function(){
                $(this).remove();
                ydOrder.update_view();
            });
            return false;
        }

        callMeal(mealId, sizeId);
        return false; //avoid next load of meal from tr
    });

    /**
     * when we select another size
     * @author alex
     * @since 28.07.2011
     */
    $(".yd-change-size").live('click', function(){
        var form = this.form;
        var oldSizeId = form.sizeId.value;
        var oldSizeName = form.sizeName.value;
        var sizeId = this.value;
        var sizeName = form["sizes_" + sizeId + "_name"].value;
        var sizeNameEncoded = encodeURIComponent(sizeName).replace(/%/g,'');
        var cost = parseInt(form["sizes_" + sizeId + "_cost"].value);
        var priceType = $("#yd-meal-price-type").val();

        $('span.yd-dbh-extras a').each(function(){
            removeExtra(this);
        });

        $('.yd-current-extras').hide();
        $('#yd-current-extras-' + sizeId).show();

        $('.yd-current-mealoption-row').hide();
        $('.yd-current-mealoption-row-size-' + sizeNameEncoded).show();
        
        var optionCosts = new Array();
        var maxOptionCost = 0;
        $('.yd-mealoption-item:checked').each(function() {
            this.checked=false;
            var parts = this.id.split('-');
            var i = parseInt(parts[4]);
            if($('#row-' + parts[1] + '-' + parts[2] + '-' + sizeNameEncoded + '-' + parts[4]).length) {
                $('#row-' + parts[1] + '-' + parts[2] + '-' + sizeNameEncoded + '-' + parts[4]).prop("checked", true);
                optionCosts[i] = parseInt(form["options_" + parts[1] + "_" + parts[2] + "_" + sizeNameEncoded + "_cost"].value);                
                if (priceType == "options_avg") {
                    optionCosts[i] = optionCosts[i]/parseInt($('#choices-' + parts[1]).attr('data-max-choices'));
                }
                document.lastMealOptionCost[i] = optionCosts[i];
                if (priceType == "options_max") {
                    maxOptionCost = Math.max(maxOptionCost, optionCosts[i]);
                }
            }
        });
        if (optionCosts.length) {
            $.each(optionCosts, function(i) {
                if (priceType == "options_max") {
                    if (maxOptionCost == this) {
                        cost += this;
                        maxOptionCost = -1;
                    } else {
                        document.lastMealOptionCost[i] = 0
                    }
                } else {
                    cost += this;
                }
            });
        }

        $("#yd-meal-cost-base").val(cost);
        $("#yd-meal-size-hidden").val(this.value)
        $(".yd-current-meal-price").html(int2price(cost));
        form.sizeId.value = this.value;
        form.sizeName.value = sizeName;
    });


    /**
     * append all choices to the current header box of an option row
     * @author mlaug
     * @since 08.11.2011
     */
    function appendChoices(checkboxes, row, change){
        var maxChoices = $('[id^=choices-]').attr('data-max-choices');
        var choices = row.find('.yd-dbh-choise');
        choices.html('');
        var changeLink = row.find('.yd-dbh-change').show();
        if ( change ){
            changeLink.show();
        }
        else{
            changeLink.hide();
        }
        $.each(checkboxes,function(index){
            var opt = $(this).next().html();
            log('appending ' + opt);
            if (index > 0 && index != maxChoices-1) {
                choices.append(', ');
            }
            if (maxChoices > 1 && index == maxChoices-1) {
                choices.append(' & ');
            }
            choices.append($.trim(opt));
        });

        //update selection
        row.find('.yd-option-choices-selected').html(checkboxes.length);
    }

    /**
     * change the current option selection
     * @author mlaug
     * @since 08.11.2011
     */
    $('.yd-change-options').live('click',function(event){
        // user clicked on "change" link in the green header, so don't propagate the click to the parent'
        var headerElement = '';
        if ($(this).hasClass('yd-dbh-change')) {
            headerElement = $(this).parent();
            event.stopPropagation();
        }
        // user clicked directly on the surrounding green header
        else {
            headerElement = $(this);
        }

        var row  = headerElement.closest(".optrow");
        var checkboxes = row.find(":checkbox:checked");
        var maxChoices = row.attr('data-max-choices');

        if (checkboxes.length >= maxChoices) {

            $.each($('.yd-change-options'), function(){
                if (headerElement.context.tagName == 'SPAN') {
                    headerElement.toggle();
                }
            })
            
            $('#yd-change-options-box-' + headerElement.attr('id').split('-')[3]).toggle();
        }
    });

    /**
     * check that options are only as much as allowed and calculate sum cost
     * @author vpriem, alex
     * @since 19.07.2011
     */
    $('.yd-check-option').live('click', function() {
        log('trying to check option');

        var optionRowId = parseInt(this.id.split('-')[1]);
        var optionId = parseInt(this.id.split('-')[2]);
        var optionNum = 0;

        var $optionRow = $('#choices-' + optionRowId);
        var choicesMin = parseInt($optionRow.attr('data-min-choices'));
        var choicesMax = parseInt($optionRow.attr('data-max-choices'));

        var $row  = $(this).closest(".optrow");
        var $checkboxes = $row.find(":checkbox:checked");
        var $radioboxes = $row.find(":radio:checked");
        var numCheckboxes = $checkboxes.length;
        var numRadioboxes = $radioboxes.length;
        if (numRadioboxes) {
            optionNum = parseInt(this.id.split('-')[4]);
        }

        var form = this.form;

        var mealCost = $("#yd-meal-cost-base").intVal();
        var priceType = $("#yd-meal-price-type").val();
        var sizeId = $("#yd-meal-size-hidden").val();
        var sizeName = form["sizes_" + sizeId + "_name"].value;
        var sizeNameEncoded = encodeURIComponent(sizeName).replace(/%/g,'');
        if (numRadioboxes) {
            var optionCost = parseInt(form["options_" + optionRowId + "_" + optionId + "_" + sizeNameEncoded + "_cost"].value);
        } else {
            var optionCost = parseInt(form["options_" + optionRowId + "_" + optionId + "_cost"].value);
        }
        if (priceType == "options_avg") {
            optionCost /= choicesMax;
        }
        if (!document.lastMealOptionCost) {
            document.lastMealOptionCost = new Array();
        }

        if ($(this).is(":checked")) {
            if (numRadioboxes) {
                if (priceType == "options_max") {
                    var maxMealCost = 0;
                    for (var i=0; i < choicesMax; i++) {
                        if (document.lastMealOptionCost[i]) {
                            //mealCost -= document.lastMealOptionCost[i];
                            if (document.lastMealOptionCost[i]>maxMealCost && i!=optionNum) {
                                maxMealCost = document.lastMealOptionCost[i];
                            }
                        }
                    }
                    document.lastMealOptionCost[optionNum]=optionCost;
                    maxMealCost = Math.max(optionCost, maxMealCost)
                    mealCost = maxMealCost;
                    for (var i=0; i < choicesMax; i++) {
                        if (document.lastMealOptionCost[i]) {
                            if (document.lastMealOptionCost[i] == maxMealCost) {
                                maxMealCost = -1;
//                            } else {
                                //document.lastMealOptionCost[i] = 0;
                            }
                        }
                    }
                } else {
                    if (document.lastMealOptionCost[optionNum]) {
                        mealCost -= document.lastMealOptionCost[optionNum];
                    }
                    document.lastMealOptionCost[optionNum]=optionCost;
                    mealCost += optionCost;
                }
            } else {
                mealCost += optionCost;
            }
        }
        else {
            mealCost = mealCost - optionCost;
        }

        $("#yd-meal-cost-base").val(mealCost);
        $(".yd-current-meal-price").html(int2price(mealCost));

        //you can choose more, dude!
        log('selected ' + (numCheckboxes+numRadioboxes) + ' of ' + choicesMin + ' minimum options');
        if (numCheckboxes < choicesMin && numRadioboxes < choicesMin) {
            log('more choices are possible');
            $row.removeClass('yd-dialogs-green');
            if(numCheckboxes) {
                appendChoices($checkboxes, $row, false);
            }
            if(numRadioboxes) {
                $row.find('.yd-option-choices-selected').html(numRadioboxes);
            }
            return true;
        }

        //bind to box
        $('#choices-' + optionRowId + '-1').one('click', function(){
            $('#yd-change-options-' + optionRowId).hide();
            $('#yd-change-options-box-' + optionRowId).show();
        });

        //you are filled up, no more choices available
        var countCheckedOptions = numCheckboxes+numRadioboxes;
        if (countCheckedOptions == choicesMax) {
            log('all choices have been made, closing box');
        }
        else if (countCheckedOptions > choicesMax){
            var $checkbox = $checkboxes.not(this).last();
            $checkbox.prop("checked", false);
            var lastOptionId = $checkbox.attr("id").split('-')[2];

            var lastOptionCost = parseInt(form["options_" + optionRowId + "_" + lastOptionId + "_cost"].value);
            mealCost = mealCost - lastOptionCost;

            $("#yd-meal-cost-base").val(mealCost);
            $(".yd-current-meal-price").html(int2price(mealCost));
            countCheckedOptions--;
        }

        if (countCheckedOptions == choicesMax && numCheckboxes) {
            $row.find('.yd-dialogs-box-body').hide();
        }

        $row.addClass('yd-dialogs-green').removeClass('warning');
        if (!$(".optrow.warning").length) {
            $('div.yd-dialogs').removeClass('warning');
        }

        if(numCheckboxes) {
            appendChoices($row.find(":checkbox:checked"), $row, countCheckedOptions == choicesMax);
        }
        if(numRadioboxes) {
            $row.find('.yd-option-choices-selected').html(numRadioboxes);
        }
        return true;
    });

    /**
     * set meal cost when some extras was selected/unselected
     * @author alex
     * @since 01.08.2011
     */
    $('button.yd-extras').live('click',function() {
        log('clicked an extra');
        addExtra(this);
        return false;
    });

    $('a.yd-extras-remove').live('click',function(){
        log('remove extra');
        removeExtra(this);
        return false;
    });

});