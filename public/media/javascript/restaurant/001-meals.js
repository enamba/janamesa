$(document).ready(function(){
    
    // add meal
    $(".yd-restaurant-addmeal").live('click',function() {
        var categoryId = $(this).attr('id').split('-')[3];
        var mealName = $('#yd-addmealname-x-' + categoryId).val();
        var categoryMwst = $('#category-mswt-' + categoryId).val();

        if ( mealName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen ein!');
            return;
        }
               
        $.post(
            "/request_restaurant/addmeal",
            $("#yd-addmeal-form-" + categoryId).serialize(),
            function(mealId){
                if (mealId.split('-')[0] == "Error") {
                    $('#yd-saving-menu-status').html(mealId.split('-')[1]);
                    return;
                }

                // we get only int values from db, so convert it to the float to compare the values with forms in htm files
                if(categoryMwst.indexOf('.') == -1) {
                    categoryMwst = categoryMwst + '.0';
                }

                $('#yd-addmealname-x-' + categoryId).val('');
                $('#yd-addmealdesc-x-' + categoryId).val('');
                $('#yd-addmeal-mwst-' + categoryId).val(categoryMwst);
                $('#yd-meal-minAmount-' + categoryId).val('1');
                $('#yd-addmeal-vegetarian-' + categoryId).prop("checked", false);
                $('#yd-addmeal-bio-' + categoryId).prop("checked", false);
                $('#yd-addmeal-spicy-' + categoryId).prop("checked", false);
                $('#yd-addmeal-garlic-' + categoryId).prop("checked", false);
                $('#yd-addmeal-fish-' + categoryId).prop("checked", false);
                $('#yd-addmeal-tabaco-' + categoryId).prop("checked", false);
                $('#yd-addmeal-excludeFromMinCost-' + categoryId).prop("checked", false);
                $('#yd-addmeal-priceType-' + categoryId).val('');
                $('.yd-addmeal-sizecost-' + categoryId).val('');
                $('.yd-addmeal-pfandcost-' + categoryId).val('');
                $('.yd-addmeal-sizenr-' + categoryId).val('');
                
                $('#yd-addmealname-x-' + categoryId).focus();

                $.ajax({
                    type: "POST",
                    url: '/request_restaurant/getmeal',
                    data: (
                    {
                        'mealId' : mealId,
                        'categoryId' : categoryId
                    }
                    ),
                    success: function(response){
                        $('#yd-meals-table-nested-' + categoryId).append(response);
                        //Set Back Mwst to original value                                             
                        var options = $("#yd-addmeal-mwst-" + categoryId).children();
                        options.each( function(index, item) {
                            if(parseInt(item.value) == parseInt(categoryMwst)) {
                                item.selected = true;
                            } else {
                                item.selected =  false;
                            }
                                              
                        });
                    }
                });

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
            }
            );
    });


    // add category
    $("#yd-save-new-category").live('click',function() {
        var categoryName = $('#yd-addcathegory-name').val();

        if ( categoryName.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben sie einen Name für die Kategorie');
            return;
        }
        
        $('#yd-restaurant-backend-wait').show();

        $.post(
            "/request_restaurant/addcategory",
            $("#yd-new-category-form").serialize(),
            function(categoryId){

                $('#yd-addcathegory-name').val('');
                $('#yd-addcathegory-description').val('');
                $('#yd-addcathegory-excludeFromMinCost').prop("checked", false);
                $('#yd-addcathegory-hasPfand').prop("checked", false);

                $('#yd-addcathegory-st1').prop("checked", true);
                $('#yd-addcathegory-st2').prop("checked", false);
                $('#yd-addcathegory-st3').prop("checked", false);
                $('#yd-addcathegory-st4').prop("checked", false);

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
                    url: '/request_restaurant/getcategory',
                    data: (
                    {
                        'categoryId' : categoryId
                    }
                    ),
                    success: function(response){
                        $('#yd-restaurant-selected-category').html(response);
                        $('#yd-restaurant-backend-wait').hide();
                    }
                });
            }
            );
    });

    // add extras to the size
    $(".yd-restaurant-addextra-size").live('click',function() {
        var restaurantId = $(this).attr('id').split('-')[3];
        var sizeId = $(this).attr('id').split('-')[4];
        var categoryId = $(this).attr('id').split('-')[5];

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
            "/restaurant_menu/extrasforsize/sizeId/" + sizeId + "/restaurantId/" + restaurantId + "/categoryId/" + categoryId,
            "Extras",
            settings);
        win.focus();
    });


    // add extras to the meal
    $(".yd-restaurant-addextra-meal").live('click',function() {
        var restaurantId = $(this).attr('id').split('-')[3];
        var sizeId = $(this).attr('id').split('-')[4];
        var mealId = $(this).attr('id').split('-')[5];
        var categoryId = $(this).attr('id').split('-')[6];

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
            "/restaurant_menu/extrasformeal/mealId/" + mealId + "/sizeId/" + sizeId + "/restaurantId/" + restaurantId + "/categoryId/" + categoryId,
            "Extras",
            settings);
        win.focus();
    });

    // manage options for the meals
    $(".yd-restaurant-options-meal").live('click',function() {
        var mealId = $(this).attr('id').split('-')[3];
        var categoryId = $(this).attr('id').split('-')[4];
        var restaurantId = $(this).attr('id').split('-')[5];

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
            "/restaurant_menu/optionsformeal/restaurantId/" + restaurantId + "/mealId/" + mealId + "/categoryId/" + categoryId,
            "Optionen",
            settings);
        win.focus();
    });

    // activate save category button when text in some text element (textfield or textarea) of the meal has been changed
    $('.yd-meal-element').live('keyup',function(){
        if ( $(this).is('input:text, textarea') ) {
            var mealId = $(this).attr('id').split('-')[3];
            var categoryId = $(this).attr('id').split('-')[4];

            $('#yd-save-category-' + categoryId).css('border','1px solid green');
            $('#yd-save-category-' + categoryId).css('background','#9ac92c');
            $('#yd-save-category-' + categoryId).css('text-shadow','1px 1px 0px rgba(0,0,0,0.5)');
            $('#yd-save-category-' + categoryId).prop("disabled", false);

            $('#yd-save-category2-' + categoryId).css('border','1px solid green');
            $('#yd-save-category2-' + categoryId).css('background','#9ac92c');
            $('#yd-save-category2-' + categoryId).css('text-shadow','1px 1px 0px rgba(0,0,0,0.5)');
            $('#yd-save-category2-' + categoryId).prop("disabled", false);

            $('#yd-update-meal-link-' + mealId + '-' + categoryId).html('<img src="/media/images/yd-backend/icon-save.png" title="Speise speichern"/>');
            $('#yd-update-meal-link-' + mealId + '-' + categoryId).attr('active', '1');
        }
    });

    // activate save category button when value of some other element of the meal has been changed - "online" checkbox or "mwst" select box
    $('.yd-meal-element').live('change',function(){
        if ( !($(this).is('input:text')) && !($(this).is('textarea')) ) {
            var mealId = $(this).attr('id').split('-')[3];
            var categoryId = $(this).attr('id').split('-')[4];

            $('#yd-save-category-' + categoryId).css('border','1px solid green');
            $('#yd-save-category-' + categoryId).css('background','#9ac92c');
            $('#yd-save-category-' + categoryId).css('text-shadow','1px 1px 0px rgba(0,0,0,0.5)');
            $('#yd-save-category-' + categoryId).prop("disabled", false);

            $('#yd-save-category2-' + categoryId).css('border','1px solid green');
            $('#yd-save-category2-' + categoryId).css('background','#9ac92c');
            $('#yd-save-category2-' + categoryId).css('text-shadow','1px 1px 0px rgba(0,0,0,0.5)');
            $('#yd-save-category2-' + categoryId).prop("disabled", false);

            $('#yd-update-meal-link-' + mealId + '-' + categoryId).html('<img src="/media/images/yd-backend/icon-save.png" title="Speise speichern"/>');
            $('#yd-update-meal-link-' + mealId + '-' + categoryId).attr('active', '1');
        }
    });

    // save meal when green button was clicked
    $('.yd-update-meal-link').live('click',function(){
        var mealId = $(this).attr('id').split('-')[4];
        var categoryId = $(this).attr('id').split('-')[5];
        var active = $('#yd-update-meal-link-' + mealId + '-' + categoryId).attr('active');

        // save meal
        if(active == 1) {
            var online = $('#yd-meal-online-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var mealName = $('#yd-meal-name-' + mealId + '-' + categoryId).val();

            if ( mealName.replace(/^\s+|\s+$/g, '') == '') {
                alert('Bitte geben Sie einen gültigen Namen ein!');
                return;
            }

            var mealDescription = $('#yd-meal-description-' + mealId + '-' + categoryId).val();
            var mealMwst = $('#yd-meal-mwst-' + mealId + '-' + categoryId).val();
            var minAmount = $('#yd-meal-minAmount-' + mealId + '-' + categoryId).val();
            var vegetarian = $('#yd-meal-vegetarian-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var bio = $('#yd-meal-bio-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var spicy = $('#yd-meal-spicy-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var garlic = $('#yd-meal-garlic-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var fish = $('#yd-meal-fish-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var tabaco = $('#yd-meal-tabaco-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var excludeFromMinCost = $('#yd-meal-excludeFromMinCost-' + mealId + '-' + categoryId).is(":checked") ? 1 : 0;
            var priceType = $('#yd-meal-priceType-' + mealId + '-' + categoryId).val();

            var sizeCosts = new Object();
            var pfandCosts = new Object();
            var sizenumbers = new Object();

            $('.yd-size-for-meal-' + mealId).each(function(){
                var sizeId = $(this).attr('id').split('-')[4];
                var cost = $('#yd-sizecost-' + sizeId + '-' + mealId + '-' + categoryId).val();
                var pfand = $('#yd-pfandcost-' + sizeId + '-' + mealId + '-' + categoryId).val();
                var nrs = $('#yd-mealsizenr-' + sizeId + '-' + mealId + '-' + categoryId).val();
                sizeCosts[sizeId] =  cost;
                pfandCosts[sizeId] =  pfand;
                sizenumbers[sizeId] =  nrs;
            });

            $.ajax({
                type: "POST",
                url: "/request_restaurant/updatemeal",
                data: (
                {
                    'mealId' : mealId,
                    'online' : online,
                    'mealName' : mealName,
                    'mealDescription' : mealDescription,
                    'mealMwst' : mealMwst,
                    'minAmount' : minAmount,
                    'vegetarian' : vegetarian,
                    'bio' : bio,
                    'spicy' : spicy,
                    'garlic' : garlic,
                    'fish' : fish,
                    'tabaco' : tabaco,
                    'excludeFromMinCost' : excludeFromMinCost,
                    'sizeCosts' : sizeCosts,
                    'pfandCosts' : pfandCosts,
                    'sizenumbers' : sizenumbers,
                    'priceType' : priceType
                }
                ),
                success: function(data){
                    if (data == 1) {
                        $('#yd-update-meal-link-' + mealId + '-' + categoryId).html('<img src="/media/images/yd-backend/icon-save-grey.png"/>');
                        $('#yd-update-meal-link-' + mealId + '-' + categoryId).attr('active', '0');

                        var allMealsSaved = true;

                        $('.yd-update-meal-from-category-' + categoryId).each(function(){
                            var isActive = $(this).attr('active');
                            if (isActive == 1) {
                                allMealsSaved = false;
                            }
                        });

                        if (allMealsSaved) {
                            $('#yd-save-category-' + categoryId).css('border','1px solid #aaa');
                            $('#yd-save-category-' + categoryId).css('background','#ddd');
                            $('#yd-save-category-' + categoryId).css('text-shadow','0 0 0');
                            $('#yd-save-category-' + categoryId).prop("disabled", true);

                            $('#yd-save-category2-' + categoryId).css('border','1px solid #aaa');
                            $('#yd-save-category2-' + categoryId).css('background','#ddd');
                            $('#yd-save-category2-' + categoryId).css('text-shadow','0 0 0');
                            $('#yd-save-category2-' + categoryId).prop("disabled", true);
                        }
                        $('#yd-category-save-status-' + categoryId).html('Speise wurde gespeichert');
                        $('#yd-category-save-status2-' + categoryId).html('Speise wurde gespeichert');
                    }
                    else {
                        $('#yd-category-save-status-' + categoryId).html(data);
                        $('#yd-category-save-status2-' + categoryId).html(data);
                    }
                }
            });
        }
    });


    // show window with meals associated with this extra
    $(".yd-restaurant-show-extra-meals").live('click',function() {
        var extraId = $(this).attr('id').split('-')[2];

        var w = 800;
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
            "/restaurant_menu/mealsforextra/extraId/" + extraId,
            "Zuordnungen",
            settings);
        win.focus();
    });

    // save the selected category
    $(".yd-save-category").live('click',function() {
        var categoryId = $(this).attr('id').split('-')[3];

        if( !($('#yd-save-category-' + categoryId).attr('disabled')) ) {
            $('#yd-restaurant-backend-wait').show();

            $('.yd-update-meal-from-category-' + categoryId).each(function(){
                var mealId = $(this).attr('id').split('-')[4];
                if( $('#yd-update-meal-link-' + mealId + '-' + categoryId).attr('active') == 1 ) {
                    $('#yd-update-meal-link-' + mealId + '-' + categoryId).click();
                }
            });

            $('#yd-category-save-status-' + categoryId).html('<font color="#f00">Kategorie wurde gespeichert!</font>');
            $('#yd-category-save-status2-' + categoryId).html('<font color="#f00">Kategorie wurde gespeichert!</font>');
            var $categoryName = $('#yd-category-name-' + categoryId).attr('name');
            setTimeout(function(){
                $('#yd-category-save-status-' + categoryId).html('');
                $('#yd-category-save-status2-' + categoryId).html('');
            }, 3000);
            setTimeout(function(){
                $('#yd-saving-menu-status').html('');
            }, 3000);

            $('#yd-restaurant-backend-wait').hide();
        }
    });

    // show fomr for new category
    $("#yd-add-category").click(function(){
        $("span", this).toggle();
        $('#yd-new-category-form').toggle();
        $('#yd-addcathegory-name').focus();
    });

    // add new size
    $(".yd-add-size").live('click', function() {
        var categoryId = this.id.split('-')[3];
        $("span", this).toggle();
        $('#yd-newsize-name-' + categoryId).val('');
        $('#yd-new-size-name-row-' + categoryId).toggle();
        $('#yd-new-size-button-row-' + categoryId).toggle();
        $('#yd-newsize-name-' + categoryId).focus();
    });

    // add new size to the category
    $(".yd-new-size-button").live('click',function() {
        var categoryId = $(this).attr('id').split('-')[4];
        var newSize = $('#yd-newsize-name-' + categoryId).val();
        var categoryOpen = $('#yd-categoryOpen-' + categoryId).val();
        $('#yd-restaurant-backend-wait').show();

        if ( newSize.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte geben Sie einen gültigen Namen für die Größe!');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/request_restaurant/addsize",
            data: (
            {
                'categoryId' : categoryId,
                'sizeName' : newSize
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-newsize-name-' + categoryId).val('');

                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/showcategorytable',
                        data: (
                        {
                            'categoryId' : categoryId,
                            'isOpen' : categoryOpen
                        }
                        ),
                        success: function(response){
                            $('#yd-restaurant-category-' + categoryId).html(response);
                            $('#yd-restaurant-backend-wait').hide();
                        }
                    });
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
    });

    // delete meal and hide meal row in the table or show error
    $(".yd-remove-meal").live('click',function() {
        if (!confirm('Soll diese Speise wirklich gelöscht werden?'))
            return;

        var mealId = $(this).attr('id').split('-')[3];
        var categoryId = $(this).attr('id').split('-')[4];

        $.ajax({
            type: "POST",
            url: "/request_restaurant/removemeal",
            data: (
            {
                'mealId' : mealId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-meal-row-' + mealId).fadeOut(200);
                    $('#yd-meal-underline-' + mealId).fadeOut(200);
                    $('#yd-meal-data-' + mealId).fadeOut(200);
                    $('#yd-meal-types-' + mealId).fadeOut(200);

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
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
    });

    // delete meal category and hide category table or show error
    $(".yd-remove-category").live('click',function() {
        if (!confirm('Soll diese Kategorie wirklich gelöscht werden?'))
            return;

        var categoryId = $(this).attr('id').split('-')[3];
        var restaurantId = $(this).attr('id').split('-')[4];
        $('#yd-restaurant-backend-wait').show();

        $.ajax({
            type: "POST",
            url: "/request_restaurant/removecategory",
            data: (
            {
                'categoryId' : categoryId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-category-box-' + categoryId).fadeOut(200);

                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/getcategorieslist',
                        data: (
                        {
                            'restaurantId' : restaurantId
                        }
                        ),
                        success: function(response){
                            $('#yd-restaurant-categories-links').html(response);
                            $('#yd-restaurant-backend-wait').hide();
                        }
                    });
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
        return false;
    });

    // delete meal size
    $(".yd-remove-size").live('click',function() {
        if (!confirm('Soll diese Größe wirklich gelöscht werden?'))
            return;

        var sizeId = $(this).attr('id').split('-')[3];
        var categoryId = $(this).attr('id').split('-')[4];
        var categoryOpen = $('#yd-categoryOpen-' + categoryId).val();
        $('#yd-restaurant-backend-wait').show();

        $.ajax({
            type: "POST",
            url: "/request_restaurant/removesize",
            data: (
            {
                'sizeId' : sizeId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/showcategorytable',
                        data: (
                        {
                            'categoryId' : categoryId,
                            'isOpen' : categoryOpen                                    
                        }
                        ),
                        success: function(response){
                            $('#yd-restaurant-category-' + categoryId).html(response);
                            $('#yd-restaurant-backend-wait').hide();
                        }
                    });
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
    });

    // move size
    $(".yd-move-size").live('click',function() {
        var direction = $(this).attr('id').split('-')[3];
        var sizeId = $(this).attr('id').split('-')[4];
        var categoryId = $(this).attr('id').split('-')[5];
        var categoryOpen = $('#yd-categoryOpen-' + categoryId).val();
        $('#yd-restaurant-backend-wait').show();

        $.ajax({
            type: "POST",
            url: "/request_restaurant/movesize",
            data: (
            {
                'direction' : direction,
                'sizeId'    : sizeId,
                'categoryId': categoryId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/showcategorytable',
                        data: (
                        {
                            'categoryId' : categoryId,
                            'isOpen' : categoryOpen
                        }
                        ),
                        success: function(response){
                            $('#yd-restaurant-category-' + categoryId).html(response);
                            $('#yd-restaurant-backend-wait').hide();
                        }
                    });
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
        return false;
    });

    // move meal
    $(".yd-move-meal").live('click',function() {
        var direction = $(this).attr('id').split('-')[3];
        var mealId = $(this).attr('id').split('-')[4];
        var categoryId = $(this).attr('id').split('-')[5];
        var categoryOpen = $('#yd-categoryOpen-' + categoryId).val();
        $('#yd-restaurant-backend-wait').show();

        $.ajax({
            type: "POST",
            url: "/request_restaurant/movemeal",
            data: (
            {
                'direction' : direction,
                'mealId'    : mealId,
                'categoryId': categoryId
            }
            ),
            success: function(data){
                if (data == 1) {
                    $.ajax({
                        type: "POST",
                        url: '/request_restaurant/showcategorytable',
                        data: (
                        {
                            'categoryId' : categoryId,
                            'isOpen' : categoryOpen
                        }
                        ),
                        success: function(response){
                            $('#yd-restaurant-category-' + categoryId).html(response);
                            $('#yd-restaurant-backend-wait').hide();
                        }
                    });
                }
                else {
                    $('#yd-category-save-status-' + categoryId).html(data);
                    $('#yd-category-save-status2-' + categoryId).html(data);
                }
            }
        });
        return false;
    });

    // open category when th link in the upper table was clicked
    $(".yd-categorylink-js").live('click',function() {
        $('#yd-restaurant-backend-wait').show();
        var categoryId = $(this).attr('id').split('-')[2];

        $.ajax({
            type: "POST",
            url: '/request_restaurant/getcategory',
            data: (
            {
                'categoryId' : categoryId
            }
            ),
            success: function(response){
                $('#yd-restaurant-selected-category').html(response);

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
                        $('#yd-restaurant-backend-wait').hide();

                        /**
                        * add meal as an option
                        * @author Jens Naie <naie@lieferando.de> 
                        * @since 22.08.2012
                        */
                        $(".yd-add-mealoption-button").click(function() {
                            var mealId = this.id.split('-')[4];
                            var optionRowId = $('#yd-add-mealoption-select-' + mealId).val();
                            var optionRowName = $('#yd-add-mealoption-select-' + mealId + ' :selected').text();
                            if(optionRowId != '0') {
                                $.ajax({
                                    url: "/administration_request_service_meals_mealoptions/add",
                                    data: {
                                        mealId : mealId,
                                        optionRowId : optionRowId
                                    },
                                    type: "POST",
                                    dataType: "json",
                                    success: function(data){
                                        if (data.error) {
                                            return notification('error', data.error);
                                        }

                                        if (data.id) {
                                            var mealoptionNnId = data.id;
                                            $nnButton = $('<a id="yd-meal-' + mealId + '-mealoption-nn-' + mealoptionNnId + '" class="yd-meal-mealoption-nn-button">' + optionRowName + '</a>');
                                            $('#yd-meal-mealoption-rows-' + mealId).append($nnButton);
                                            $nnButton.click(function() {
                                                $.ajax({
                                                    url: "/administration_request_service_meals_mealoptions/remove",
                                                    data: {
                                                        mealoptionNnId : mealoptionNnId
                                                    },
                                                    type: "POST",
                                                    dataType: "json",
                                                    success: function(data){
                                                        if (data.error) {
                                                            return notification('error', data.error);
                                                        }

                                                        if (data.id) {
                                                            $('#yd-meal-' + mealId + '-mealoption-nn-' + data.id).remove();
                                                        }
                                                    }
                                                });

                                                return false;
                                            });
                                        }
                                    }
                                });
                            }
                            return false;
                        });

                        /**
                        * add meal as an option
                        * @author Jens Naie <naie@lieferando.de> 
                        * @since 22.08.2012
                        */
                        $(".yd-meal-mealoption-nn-button").click(function() {

                            var mealoptionNnIdSplit = this.id.split('-');
                            var mealoptionNnId = mealoptionNnIdSplit[5];
                            var mealId = mealoptionNnIdSplit[2];

                            $.ajax({
                                url: "/administration_request_service_meals_mealoptions/remove",
                                data: {
                                    mealoptionNnId : mealoptionNnId
                                },
                                type: "POST",
                                dataType: "json",
                                success: function(data){
                                    if (data.error) {
                                        return notification('error', data.error);
                                    }

                                    if (data.id) {
                                        $('#yd-meal-' + mealId + '-mealoption-nn-' + data.id).remove();
                                    }
                                }
                            });

                            return false;
                        });

                    }
                });
            }
        });

    });

    // then the price for extras group is entered, set price for all corresponding extras
    $('.yd-extrasgroup-cost').live('keyup',function(){
        var extrasgroupId = $(this).attr('id').split('-')[3];
        var cost = $(this).val();
        $('.yd-extras-cost-of-group-' + extrasgroupId).val(cost);

    });

    // set field content to empty when no content was entered yet
    $(".yd-change-all-fields").live('click',function() {
        var content = $(this).val();
        if (content == 'Nicht ändern')
            $(this).css('color','#555');
        $(this).val('');
    });

    // if nothing was entered, set info text and black font
    $(".yd-change-all-fields").live('blur',function() {
        var content = $(this).val();
        if (content == '') {
            $(this).css('color','#999');
            $(this).val('Nicht ändern');
        }
    });

});