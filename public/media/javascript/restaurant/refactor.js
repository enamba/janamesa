/**
 * validated 10.02.2010
 */

function checkUnsaved() {
    if (!checkAllSaved()) {
        return ('Es gibt noch ungespeicherte Kategorien!');
    }
}

function checkAllSaved() {
    var allSaved = true;
    $('.yd-save-categoryUpper').each(function(){
        var categoryId = $(this).attr('id').split('-')[3];
        if( $('#yd-save-category-' + categoryId).attr('disabled') == false) {
            allSaved = false;
        }
    });
    return allSaved;
}

$(document).ready(function(){

    /**
     * jQuery UI CSS only for Datepicker
     * @author oknoblich
     * @since 08.09.2011
     */
    $('#ui-datepicker-div').wrap('<div class="yd-jquerycss-prison"></div>');

    // update select box with plz when the value of select box with starting number changed
    $('.yd-plzind-select').live('change',function(){
        var plzind = $(this).val();
        var type = $(this).attr('id').split('-')[3];
        $('#yd-admin-backend-wait').show();

        $.ajax({
            type: "POST",
            url : '/request_administration/getcities',
            data: (
            {
                'plzind'    : plzind
            }
            ),
            success: function(data){
                $('#yd-plzvalues-select-' + type).html(data);
                $('#yd-admin-backend-wait').hide();
            }
        });
    });

    // check/uncheck all locations checkboxes in the locations table
    $('#yd-check-all-locations').live('click',function(){
        if ($(this).is(":checked")) {
            $('.yd-locations-checkbox').prop("checked", true);
        }
        else {
            $('.yd-locations-checkbox').prop('checked', false);
        }
    });

    // check/uncheck all tags checkboxes in the tags list
    $('#yd-check-all-tags').live('click',function(){
        if ($(this).is(":checked")) {
            $('.yd-restaurant-tag-checkbox').prop("checked", true);
        }
        else {
            $('.yd-restaurant-tag-checkbox').prop("checked", false);
        }
    });

    // check/uncheck all checkboxes of the defined class on this page
    $('.yd-check-all-checkboxes').live('click', function(){
        $('.yd-check-all-checkboxes').prop('checked', this.checked);
        $('.yd-checkbox').prop('checked', this.checked)
    });

    $('.yd-meal-of-category-row').mouseover(function() {
        var categoryId = $(this).attr('id').split('-')[5];
        $(this).css('background','#aaf');
        $("#yd-top-category-" + categoryId).css('background','#ddf');
    });

    $('.yd-meal-of-category-row').mouseout(function() {
        var categoryId = $(this).attr('id').split('-')[5];
        $(this).css('background','#fff');
        $("#yd-top-category-" + categoryId).css('background','#fff');
    });

    $('.yd-top-category-row').mouseover(function() {
        var categoryId = $(this).attr('id').split('-')[3];
        $(this).css('background','#ddf');
        $(".yd-meal-of-category-row-" + categoryId).css('background','#aaf');
    });

    $('.yd-top-category-row').mouseout(function() {
        var categoryId = $(this).attr('id').split('-')[3];
        $(this).css('background','#fff');
        $(".yd-meal-of-category-row-" + categoryId).css('background','#fff');
    });


    // mwst in category edit form changed, show prompt box
    $('#yd-edit-category-mwst').live('change',function(){
        if (confirm('Soll dieser mwst Wert auch für alle enthaltene Speisen gesetzt werden?')) {
            $('#yd-edit-category-mwstforsizes').prop("checked", true);
        }
        else {
            $('#yd-edit-category-mwstforsizes').prop("checked", false);
        }
    });

    // pop-up window with extras or options
    $(".yd-preview-popup").live('click',function() {
        var calledAction = $(this).attr('id').split('-')[2];
        var restaurantId  = $(this).attr('id').split('-')[3];
        var sizeId= $(this).attr('id').split('-')[4];
        var mealId= $(this).attr('id').split('-')[5];

        var w = 600;
        var h = 700;
        var winl = (screen.width-w)/2;
        var wint = (screen.height-h)/2 - 50;
        if (winl < 0) winl = 0;
        if (wint < 0) wint = 0;
        var settings = 'height=' + h + ',';
        settings += 'width=' + w + ',';
        settings += 'top=' + wint + ',';
        settings += 'left=' + winl + ',';
        settings += "status=0, location=0, scrollbars=1, resizable=yes, menubar=no";

        var win = window.open(
            "/restaurant_menu/preview" + calledAction + "/restaurantId/" + restaurantId + "/sizeId/" + sizeId + "/mealId/" + mealId,
            "",
            settings);
        win.focus();
    });


    /**
     * add meal to canteen meal category
     * @author alex
     * @since 25.08.2010
     */

    $(".yd-canteen-addmeal").live('click',function() {
        var categoryId = $(this).attr('id').split('-')[3];
        var mealName = $('#yd-addmealname-' + categoryId).val();
        var mealCost = $('#yd-addmeal-sizecost-' + categoryId).val();
        var categoryMwst = $(this).attr('id').split('-')[4];

        if ( mealName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen!');
            return;
        }

        if ( mealCost.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen Preis ein!');
            return;
        }

        $.post(
            "/restaurant_canteen/addmeal",
            $("#yd-addmeal-form-" + categoryId).serialize(),
            function(mealId){
                $('#yd-addmealname-' + categoryId).val('');
                $('#yd-addmealdesc-' + categoryId).val('');
                $('#yd-addmeal-mwst-' + categoryId).val(categoryMwst);
                $('#yd-addmeal-sizecost-' + categoryId).val('');

                if (mealId.split('-')[0] == 'error') {
                    $('#yd-canteen-errors').html(mealId.split('-')[1]);
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '/request_canteen/getmeal',
                    data: (
                    {
                        'mealId' : mealId
                    }
                    ),
                    success: function(response){
                        $('#yd-mealcategory-table-' + categoryId).append(response);
                    }
                });
            }
            );
    });

    /**
     * delete meal from canteen category and hide meal row in the table or show error
     * @author alex
     * @since 25.08.2010
     */
    $(".yd-canteen-remove-meal").live('click',function() {
        if (!confirm('Soll diese Speise wirklich gelöscht werden?'))
            return;

        var mealId = $(this).attr('id').split('-')[4];

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/removemeal",
            data: (
            {
                'mealId' : mealId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-meal-' + mealId).fadeOut(200);
                }
                else {
                    $('#yd-canteen-errors').html(data);
                }
            }
        });
    });

    /**
     * update canteen meal when 'edit' link was clicked
     * @author alex
     * @since 25.08.2010
     */
    $('.yd-canteen-update-meal').live('click',function(){
        var mealId = $(this).attr('id').split('-')[4];
        var mealName = $('#yd-meal-name-' + mealId).val();
        var mealDescription = $('#yd-meal-description-' + mealId).val();
        var mealSizeCost= $('#yd-meal-sizecost-' + mealId).val();
        var mealMwst = $('#yd-meal-mwst-' + mealId).val();
        var mealVegetarian = $('#yd-meal-vegetarian-' + mealId).is(":checked") ? 1 : 0;

        if ( mealName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen ein!');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/updatemeal",
            data: (
            {
                'mealId'            : mealId,
                'mealName'          : mealName,
                'mealDescription'   : mealDescription,
                'mealMwst'          : mealMwst,
                'mealVegetarian'    : mealVegetarian,
                'sizeCost'          : mealSizeCost
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-canteen-update-meal-' + mealId).fadeOut(100);
                }
                else {
                    $('#yd-canteen-errors').html(data);
                }
            }
        });
    });

    /**
     * add category to canteen
     * @author alex
     * @since 26.08.2010
     */

    $(".yd-canteen-addcategory").live('click',function() {
        var date = $(this).attr('id').split('-')[3];
        var canteenId = $(this).attr('id').split('-')[4];

        var categoryName = $('#yd-addcategory-name-' + date).val();
        var categoryDescription = $('#yd-addcategory-desc-' + date).val();
        var categoryMwst = $('#yd-addcategory-mwst-' + date).val();

        if ( categoryName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen!');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/addmealcategory",
            data: (
            {
                'canteenId'             : canteenId,
                'date'                  : date,
                'categoryName'          : categoryName,
                'categoryDescription'   : categoryDescription,
                'categoryMwst'          : categoryMwst
            }
            ),
            success: function(categoryId){
                $('#yd-addcategory-name-' + date).val('');
                $('#yd-addcategory-desc-' + date).val('');
                $('#yd-addcategory-mwst-' + date).val('19');

                if (categoryId.split('-')[0] == 'error') {
                    $('#yd-canteen-errors').html(categoryId.split('-')[1]);
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '/request_canteen/getmealcategory',
                    data: (
                    {
                        'categoryId' : categoryId
                    }
                    ),
                    success: function(response){
                        $('#yd-mealcategories-' + date).append(response);
                    }
                });
            }
        });
    });

    /**
     * update canteen category when 'edit' link was clicked
     * @author alex
     * @since 26.08.2010
     */
    $('.yd-canteen-update-category').live('click',function(){
        var categoryId = $(this).attr('id').split('-')[4];

        var categoryName = $('#yd-category-name-' + categoryId).val();
        var categoryDescription = $('#yd-category-description-' + categoryId).val();
        var categoryMwst = $('#yd-category-mwst-' + categoryId).val();

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/updatecategory",
            data: (
            {
                'categoryId'            : categoryId,
                'categoryName'          : categoryName,
                'categoryDescription'   : categoryDescription,
                'categoryMwst'          : categoryMwst
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-canteen-update-category-' + categoryId).fadeOut(100);
                }
                else {
                    $('#yd-canteen-errors').html(data);
                }
            }
        });
    });

    /**
     * activate save meal link when text in some text element (textfield or textarea) of the meal has been changed
     * @author alex
     * @since 25.08.2010
     */
    $('.yd-canteen-meal-element').live('keyup',function(){
        if ( $(this).is('input:text, textarea') ) {
            var mealId = $(this).attr('id').split('-')[3];
            $('#yd-canteen-update-meal-' + mealId).show();
        }
    });

    /**
     * activate save meal link when value of some element of the meal has been changed - some checkbox or "mwst" select box
     * @author alex
     * @since 25.08.2010
     */
    $('.yd-canteen-meal-element').live('change',function(){
        if ( !($(this).is('input:text')) && !($(this).is('textarea')) ) {
            var mealId = $(this).attr('id').split('-')[3];
            $('#yd-canteen-update-meal-' + mealId).show();
        }
    });

    /**
     * activate save category link when text in some text element (textfield or textarea) of the category has been changed
     * @author alex
     * @since 26.08.2010
     */
    $('.yd-canteen-category-element').live('keyup',function(){
        if ( $(this).is('input:text, textarea') ) {
            var categoryId = $(this).attr('id').split('-')[3];
            $('#yd-canteen-update-category-' + categoryId).show();
        }
    });

    /**
     * activate save category link when value of some element of the category has been changed - some checkbox or "mwst" select box
     * @author alex
     * @since 26.08.2010
     */
    $('.yd-canteen-category-element').live('change',function(){
        if ( !($(this).is('input:text')) && !($(this).is('textarea')) ) {
            var categoryId = $(this).attr('id').split('-')[3];
            $('#yd-canteen-update-category-' + categoryId).show();
        }
    });

    /**
     * save default category and show forms for meal adding
     * @author alex
     * @since 25.08.2010
     */
    $('.yd-canteen-default-category-save').live('click',function(){
        var nr = $(this).attr('id').split('-')[5];
        var timestamp = $(this).attr('id').split('-')[6];
        var canteenId = $(this).attr('id').split('-')[7];

        var categoryName = $('#yd-default-category-name-' + nr + '-' + timestamp).val();
        var categoryDescription = $('#yd-default-category-description-' + nr + '-' + timestamp).val();
        var categoryMwst = $('#yd-default-category-mwst-' + nr + '-' + timestamp).val();

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/adddefaultcategory",
            data: (
            {
                'canteenId'             : canteenId,
                'date'                  : timestamp,
                'categoryName'          : categoryName,
                'categoryDescription'   : categoryDescription,
                'categoryMwst'          : categoryMwst
            }
            ),
            success: function(categoryId){
                if (categoryId.split('-')[0] == 'error') {
                    $('#yd-canteen-errors').html(mealId.split('-')[1]);
                }
                else {
                    $.ajax({
                        type: "POST",
                        url: '/request_canteen/getmealcategory',
                        data: (
                        {
                            'categoryId' : categoryId
                        }
                        ),
                        success: function(response){
                            $('#yd-canteen-default-category-' + nr + '-' + timestamp).html(response);
                            $('#yd-canteen-default-category-' + nr + '-' + timestamp).html(response);
                            $('#yd-canteen-default-category-' + nr + '-' + timestamp).removeClass('yd-canteen-default-category');
                        }
                    });
                }
            }
        });
    });

    /**
     * delete meal category and hide category or show error
     * @author alex
     * @since 26.08.2010
     */
    $(".yd-canteen-remove-category").live('click',function() {
        if (!confirm('Soll diese Kategorie wirklich gelöscht werden?'))
            return;

        var categoryId = $(this).attr('id').split('-')[4];

        $.ajax({
            type: "POST",
            url: "/restaurant_canteen/removecategory",
            data: (
            {
                'categoryId' : categoryId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-canteen-category-header-' + categoryId).fadeOut(200);
                    $('#yd-mealcategory-content-' + categoryId).fadeOut(200);
                }
                else {
                    $('#yd-canteen-errors').html(data);
                }
            }
        });
    });

    // change time on start of order
    // attach this always because of the dynamic repeat order window
    function initTimepicker(start) {
        var stime = new Date(0, 0, 0, 0, 0, 0);
        if(start=='now') {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            if($("#group-name").length>0){
                minutes+=20;
            }
            while(minutes%15 > 0){
                minutes++;
            }
            //if(minutes==60) minutes='00';
            stime = new Date(0, 0, 0, hours, minutes, 0);
            var acttime = $("#deliver-time").val().split(":");
            if($("#deliver-time").val() == "" || acttime.length<2 || hours>acttime[0] || (hours==acttime[0] && minutes>acttime[1])){
                if($("#group-name").length>0){
                    $("#deliver-time").val(stime.getHours()+':'+stime.getMinutes());
                }else{
                    $("#deliver-time").val("sofort");
                }
            }
        }

        if(start=='then'){
            if($("#deliver-time").val()=="sofort"){
                $("#deliver-time").val('10:00');
            }
        }
    }

    //color offline elements in grid
    if ( $('#yd-restaurant-grid').length > 0 ){
        $('.onlineState').each(function(){
            var state = $(this).attr('id').split('-')[3];
            if (state == 0){
                $(this).parent().parent().css('color','#aaa');
            }
        });
    }


    if( $('#deliver-time').length>0){
        initTimepicker('now');
    }

    $("#deliver-time-day").live('change',function() {
        if($(this).val()=='0'){
            initTimepicker('now');
        }else{
            initTimepicker('then');
        }
    });

    /**
     * add new size on when <enter> was klicked
     * @author alex
     * @since 05.10.2010
     */
    $('.yd-newsize-name').live('keyup',function(e){
        var categoryId = $(this).attr('id').split('-')[3];

        if (e.keyCode != 13) {
            return;
        }

        var newSize = $('#yd-newsize-name-' + categoryId).val();
        if ( newSize.replace(/^\s+|\s+$/g, '') == '') {
            return;
        }

        $('#yd-new-size-button-' + categoryId).click();
    });

    /**
     * create meal when <enter> in one of corresponding fields was klicked
     * @author alex
     * @since 05.10.2010
     */
    $('.yd-new-meal-active-element').live('keyup',function(e){
        var categoryId = $(this).attr('id').split('-')[3];

        if (e.keyCode != 13) {
            return;
        }

        $('#yd-restaurant-addmeal-' + categoryId).click();
    });

    // on klick - change the "checked" status of the restaurant
    $('.yd-change-checked-status').live('click', function(){
        var id = this.id.split('-')[4];

        $.ajax({
            type: "POST",
            url : '/request_restaurant/togglechecked',
            data: {
                'restaurantId': id
            },
            success: function(data){
                $(".yd-change-checked-status-panel").hide();
                if (data == 1) {
                    $("#yd-change-checked-status-panel-1").show();
                }
                else if (data == 0) {
                    $("#yd-change-checked-status-panel-0").show();
                }
                else {
                    alert(data);
                }
            }
        });

        return false;
    });

    /**
     * change the color of drop-down box when status changes
     */
    $('#yd-restaurant-settings-status').live('change',function(){
        var status = $(this).val();
        if (status == 0) {
            $(this).css('background-color', '#f99');
        }
        else {
            $(this).css('background-color', '#9f9');
        }
    });

    // date- and timepicker
    if ( $('#yd-special-openings').length > 0){
        initDatepicker('now', 'yd-special-openings');
    }

    // datepicker for modifying the special openings
    $('.yd-special-openings-edit').each(function(){
        $(this).datepicker({
                minDate: new Date(),
                maxDate: '+52w'
            });
    });

    // date- and timepicker for vacancy
    if ( $('#yd-vacation-from').length > 0){
        initDatepicker('now', 'yd-vacation-from');
    }

    if ( $('#yd-vacation-until').length > 0){
        initDatepicker('now', 'yd-vacation-until');
    }


    /**
     * add defined mwst to all prices in this meal category
     * @author alex
     * @since 15.10.2010
     */
    $('.yd-set-all-costs').live('click',function(){
        var categoryId = $(this).attr('id').split('-')[4];
        var change = $('#yd-set-all-costs-value-' + categoryId).val();

        var changeInt = parseInt(change);
        if (isNaN(changeInt) || changeInt<=0) {
            return;
        }

        $('.yd-cost-' + categoryId).each(function(){
            // replace comma wiht period so js can parse it as float
            var cost = parseFloat($(this).val().replace(",", "."));

            // somulate entered char, so we activate links to save meal and save category
            $(this).keyup();

            if (!isNaN(cost) && cost>0) {
                //add this mwst to the cost
                var newcost = cost*changeInt/100;
                // round the number so we have only two digits after the period
                newcost = Math.round(newcost*100)/100;

                newcost += '';
                newcost = newcost.replace(".", ",");

                var comma = newcost.indexOf(',');
                //we have int number, so must pad it with comma and zeroes on the right side
                if (comma == -1){
                    newcost = newcost + ',00';
                }
                //we have only one number after comma, pad it with zero on the right side
                else if ((newcost.substring(comma)).length == 2) {
                    newcost = newcost + '0';
                }
                $(this).val(newcost);
            }
        });

    });

    // standard settings for prompts
    jQuery.prompt.setDefaults({
        prefix: 'promptmsg',
        overlayspeed: 'fast',
        zIndex: '50',
        opacity: '0.4'
    });

    // even and odd rows for tables
    function evenodd(){
        jQuery("tr:even").addClass("even");
        jQuery("tr:odd").addClass("odd");
    }
    evenodd();

    $('.remove-openings').live('click',function(){
        var id = $(this).attr('id').split('-');
        alert(id);
    });

    $('.addopening').live('click',function(){});

    $("#sortable").disableSelection();

    $( "#sortable" ).sortable(
    {
        placeholder: 'ui-state-highlight',
        cursor: 'move',
        stop: function(event, ui) {
            var catArray = $('#sortable').sortable('toArray');
            var restaurantId = $('#restaurantId').val();

            $.ajax({
                type: "POST",
                url: "/request_restaurant/arrangecategories",
                data: (
                {
                    'categories' : catArray,
                    'restaurantId' : restaurantId
                }
                ),
                success: function(data){
                    if (data == 1) {
                        location.reload();
                    }
                    else {
                        $('#yd-categories-arrange-status').html(data);
                    }
                }
            });
        }
    });

    // mark menu as new
    $('.yd-mark-menu-as-new').live('click',function(){
        var rid = $(this).attr('id').split('-')[3];
        $.post("/request_restaurant/markmenu", {
            asnew : '1',
            restaurantId : rid
        }, function(response) {
            $('#yd-setting-newmenu-status').html(response);
        });
    });

    // mark menu as old
    $('.yd-mark-menu-as-old').live('click',function(){
        var rid = $(this).attr('id').split('-')[3];
        $.post("/request_restaurant/markmenu", {
            asnew : '0',
            restaurantId : rid
        }, function(response) {
            $('#yd-setting-newmenu-status').html(response);
        });
    });

    //show popup with orders edit options
    $('.yd-edit-order-options').live('click', function(){
        var id = this.id.split('-')[4];
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            loaded: function() {
                $('.promptfbuttons').hide();
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','orderedit');
                $('#orderedit').load('/request_restaurant/orderedit/orderId/'+id);

                $('#orderedit').load('/request_restaurant/orderedit/orderId/'+id, function() {
                    if ( $('#yd-order-delivertime-d').length > 0){
                        initDatepicker('now', 'yd-order-delivertime-d');
                    }
                });
            }
        });
        return false;
    });

    //close the order edit lightbox
    $('.yd-orderedit-cancel').live('click',function(){
        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','orderedit');
        jQuery.prompt.close();
    });

    //set the deliver time and close the order edit lightbox
    $('.yd-order-deliverTime').live('click',function(){
        var orderId = $(this).attr('id').split('-')[3];
        var deliverTimeD = $('#yd-order-delivertime-d').val();
        var deliverTimeT = $('#yd-order-delivertime-t').val();

        $.post("/request_restaurant/orderedit", {
            command : 'setDeliverTime',
            orderId : orderId,
            deliverTimeD : deliverTimeD,
            deliverTimeT : deliverTimeT
        },
        function() {
            });

        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','orderedit');
        jQuery.prompt.close();
        location.reload();
    });

    //set the pfand and close the order edit lightbox
    $('.yd-order-pfand').live('click',function(){
        var orderId = $(this).attr('id').split('-')[3];
        var pfand = $('#yd-order-pfand-value').val();

        $.post("/request_restaurant/orderedit", {
            command : 'setPfand',
            orderId : orderId,
            pfand : pfand
        },
        function() {
            location.reload();
        });

        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','orderedit');
        jQuery.prompt.close();
    });

    //color orders in grid
    if ( $('#yd-order-grid').length > 0 ){
        $('.status').each(function(){
            if ( $(this).html() == "Prepayment" ){
                $(this).parent().css('background-color','#bbb');
            }
            if ( $(this).html() == "Fake" ){
                $(this).parent().css('background-color','#abc');
            }
            if ( $(this).html() == "Fehlerhaft" ){
                $(this).parent().css('background-color','#ffc');
            }
            if ( $(this).html() == "Bestätigt" || $(this).html() == "Ausgeliefert" ){
                $(this).parent().css('background-color','#d5ffce');
            }
            if ( $(this).html() == "Storniert" ){
                $(this).parent().css('background-color','#ffcece');
            }
            if ( $(this).html() == "Unbestätigte Bestellung auf Rechnung" ){
                $(this).parent().css('background-color','orange');
            }
            if ( $(this).html() == "Unbestätigt" ){
                $(this).parent().css('background-color','#ffdf9f');
            }
        });
    }

    // show textarea for offline reason when status was changed
    $('#yd-offline-reason').live('change',function(){
        $('#yd-offline_change_reason').show();
        $('#yd-save-status-button').hide();
        $('#yd-status-changed-missing-reason').show();
    });

    //show save button if some text was entered in offline reason textarea
    $('#yd-offline-change-reason-text').live('keyup',function(){
        var reason = $(this).val();

        //trim string, so white spaces are not included
        reason  = reason.replace(/^\s\s*/, '').replace(/\s\s*$/, '');

        if (reason.length > 0) {
            $('#yd-status-changed-missing-reason').hide();
            $('#yd-save-status-button').show();
        }
        else {
            $('#yd-status-changed-missing-reason').show();
            $('#yd-save-status-button').hide();
        }
    });

    // show meal size edit form
    $(".yd-edit-mealsize").live("click", function(){
        var sizeId = $(this).attr('id').split('-')[3];
        $('#yd-meal-size-editname-' + sizeId).show();
        $('#yd-meal-size-newname-' + sizeId).focus();
        $('#yd-meal-size-name-container-' + sizeId).hide();
    });

    // hide meal size edit form
    $(".cancel-saving-mealsize").live("click", function(){
        var sizeId = $(this).attr('id').split('-')[4];
        $('#yd-meal-size-editname-' + sizeId).hide();
        $('#yd-meal-size-name-container-' + sizeId).show();
        $('#yd-meal-size-newname-' + sizeId).val($('#yd-meal-size-name-' + sizeId).html());
    });

    // save the meal size and hide meal size edit form
    $(".save-mealsize").live("click", function(){
        var sizeId = $(this).attr('id').split('-')[3];
        var sizeName = $('#yd-meal-size-newname-' + sizeId).val();

        if ( sizeName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen ein!');
            return;
        }

        $.post("/request_restaurant/editsize", {
            sizeId : sizeId,
            sizeName : sizeName
        }, function(data) {
            if (data) {
                if (data.error) {
                    notification('error', data.error);
                }
                else if (data.success){
                    $('#yd-meal-size-editname-' + sizeId).hide();
                    $('#yd-meal-size-name-container-' + sizeId).show();
                    $('#yd-meal-size-name-' + sizeId).html(sizeName);
                }
            }
        }, "json");
    });

    /**
     * modify size on when <enter> was klicked in the input field
     * @author alex
     * @since 15.02.2011
     */
    $('.meal-size-newname').live('keyup',function(e){
        var sizeId = $(this).attr('id').split('-')[4];

        if (e.keyCode == 27) {
            $('#yd-meal-size-editname-' + sizeId).hide();
            $('#yd-meal-size-name-container-' + sizeId).show();
            $(this).val($('#yd-meal-size-name-' + sizeId).html());
            return;
        }
        else if (e.keyCode != 13) {
            return;
        }

        var sizeName = $('#yd-meal-size-newname-' + sizeId).val();
        if ( sizeName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen ein!');
            return;
        }

        $('#yd-save-mealsize-' + sizeId).click();
    });

    // hide category edit form
    $(".cancel-editing-category").live("click", function(){
        $('.edit-category-form').hide();
    });

    // show category edit form
    $("#yd-open-edit-category-form").live("click", function(){
        $('.edit-category-form').show();
    });

    $(".edit-category-button").live('click',function() {
        var categoryId = $(this).attr('id').split('-')[4];
        $('#yd-restaurant-backend-wait').show();

        $.post(
            "/request_restaurant/editcategory",
            $("#yd-edit-category-form-" + categoryId).serialize(),
            function(){
                $.ajax({
                    type: "POST",
                    url: '/request_restaurant/getcategorieslist',
                    data: (
                    {
                        'categoryId' : categoryId
                    }
                    ),
                    success: function(response){
                        $('#yd-restaurant-categories-links').html(response);
                    }
                });


                $.ajax({
                    type: "POST",
                    url: '/request_restaurant/showcategorytable',
                    data: (
                    {
                        'categoryId' : categoryId,
                        'isOpen' : 1
                    }
                    ),
                    success: function(response){
                        $('#yd-restaurant-category-' + categoryId).html(response);
                        $('#yd-restaurant-backend-wait').hide();
                    }
                });

                $('.edit-category-form').hide();
            }
            );
    });

    /**
     * show preview lightbox with extras and options  for the meal
     * @author alex
     * @since 05.01.2011
     */
    $(".yd-meal-preview-lightbox").live("click",function(){
        var data = this.id.split('-');
        var mealId = data[1];
        var sizeId = data[2];

        openDialog('/request_restaurant/mealpreviewlightbox/id/' + mealId + '/size/' + sizeId, {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });

        return false;
    });

    /**
     * show extras and options when we select another size
     * @author alex
     * @since 11.11.2011
     */
    $(".yd-preview-change-size").live('click', function(){
        var sizeId = this.value;
        var cost = $('#yd-sizes_' + sizeId + '_cost').show().val();

        $(".yd-current-meal-price").html(int2price(cost));

        $('.yd-current-extras').hide();
        $('#yd-current-extras-' + sizeId).show();
    });

    /**
     * show/hide fields for meal-size numbers
     * @author alex
     * @since 20.12.2011
     */

    $("#yd-show-meal-numbers").live("click", function(){
        if ($(this).is(":checked")) {
            $('.yd-value-nr').show();
        }
        else {
            $('.yd-value-nr').hide();
        }
    });
    
    
    /**
     * remove opening and reload openings table
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.07.2012
     */
    $(".yd-remove-opening").live('click',function() {
        if (!confirm('Soll diese Öffnungszeit wirklich gelöscht werden?'))
            return;

        var restaurantId = $(this).attr('id').split('-')[3];
        var openingId = $(this).attr('id').split('-')[4];

        $.ajax({
            type: "POST",
            url: "/request_restaurant/removeopening",
            data: (
            {
                'openingId' : openingId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-opening-row-' + openingId).fadeOut(200);

                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/getopenings',
                        data: (
                        {
                            'restaurantId' : restaurantId
                        }
                        ),
                        success: function(response){
                            $('#yd-openings-table').html(response);
                        }
                    });
                }
                else {
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
    });    

    /**
     * activate save opening button when value of time element has been changed
     * @author Alex Vait
     * @since 19.07.2012
     */
    $('.yd-opening-element').live('change',function(){
        var openingId = $(this).attr('id').split('-')[2];

        var type = '';
        if ($(this).hasClass('yd-special-opening')) {
            type = '-special';
        }
        
        $('#yd-update-opening-link-' + openingId + type).html('<img src="/media/images/yd-backend/icon-save.png" title="Lieferzeit speichern"/>').attr('active', '1');
    });

    /**
     * show opening time dropdown when "closed" parameter changed
     * @author Alex Vait
     * @since 19.07.2012
     */
    $(".yd-special-opening-time-checkbox").live('click', function(){
        var openingId = $(this).attr('id').split('-')[2];
        if ($(this).is(":checked")) {
            $('#yd-opening-' + openingId + '-from-special').prop('disabled', '1');
            $('#yd-opening-' + openingId + '-until-special').prop('disabled', '1');
        }
        else {
            $('#yd-opening-' + openingId + '-from-special').prop('disabled', '');
            $('#yd-opening-' + openingId + '-until-special').prop('disabled', '');            
        }
    });

    /**
     * save special opening when green button was clicked
     * @author Alex Vait
     * @since 19.07.2012
     */
    $('.yd-update-opening-link').live('click',function(){
        var type = '';
        if ($(this).hasClass('yd-special-opening')) {
            type = '-special';
        }
        
        var openingId = $(this).attr('id').split('-')[4];
        var active = $('#yd-update-opening-link-' + openingId + type).attr('active');
        var restaurantId = $('#restaurantId').val();

        // save opening
        if(active == 1) {
            var from = $('#yd-opening-' + openingId + '-from' + type).val();
            var until = $('#yd-opening-' + openingId + '-until' + type).val();
            // with normal openings this values will be empty
            var date = $('#yd-opening-' + openingId + '-date').val();
            var closed = $('#yd-opening-' + openingId + '-closed').is(":checked") ? 1 : 0;                
            // with special opening this value will be empty
            var weekday = $('#yd-opening-' + openingId + '-weekday').val();


            $.post("/request_restaurant/updateopening", {
                        'type' : type,
                        'openingId' : openingId,
                        'date' : date,
                        'weekday' : weekday,
                        'from' : from,
                        'until' : until,
                        'closed' : closed,
                        'restaurantId' : restaurantId
            }, 
            function(data) {
                if (data) {
                    if (data.error) {
                        $('#yd-openings-error-' + openingId + type).show().html(data.error);
                    }
                    else if (data.success){
                        $('#yd-update-opening-link-' + openingId + type).html('<img src="/media/images/yd-backend/icon-save-grey.png"/>').attr('active', '0');
                        $('#yd-openings-error-' + openingId + type).hide().html('&nbsp');
                    }
                }
            }, "json");
        }
    });


    /**
     * delete special opening
     * @author Alex Vait 
     * @since 27.08.2012
     */
    $('.yd-delete-opening-link').live('click',function(){
        if (!confirm('Soll diese Lieferzeit wirklich gelöscht werden?'))
            return;

        var openingId = $(this).attr('id').split('-')[4];
        var restaurantId = $('#restaurantId').val();

        var type = '';
        if ($(this).hasClass('yd-special-opening')) {
            type = '-special';
        }

        $.post('/request_restaurant/removeopening', {
            'type' : type,
            'openingId' : openingId,
            'restaurantId' : restaurantId
        }, 
        function(data) {
            if (data) {
                if (data.error) {
                    alert(data.error);
                }
                else if (data.success){
                    $('#yd-opening-' + openingId + '-row' + type).fadeOut(200);
                    $('#yd-opening-' + openingId + '-error-row' + type).fadeOut(200);
                }
            }
        }, "json");
    });

});
