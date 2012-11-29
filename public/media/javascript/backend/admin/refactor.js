$(document).ready(function(){

    /**-------------------------------------------------------------------------
     * Internal links
     */
    $('#yd-link-edit-url').click(function(){
        $(this).prev().removeAttr("readonly").focus();
        return false;
    });

    /**-------------------------------------------------------------------------
     * Ratings
     */
    // delete restaurant rating
    $('a.yd-delete-rating').live('click', function(){
        if (!confirm('Soll diese Bewertung wirklich gelöscht werden?')) {
            return;
        }
        var id = this.id.split('-')[3];
        $.post("/request_administration/deleterating", {
            ratingId : id
        }, function (data) {
            if (data.length == 0) {
                $('#yd-rating-row-' + id).remove();
                notification('success', 'Bewertung wurde gelöscht');
            }
            else {
                notification('error', 'Die Bewertung konnte nicht gelöscht werden');
            }
        });
        return false;
    });

    // change status of rating
    $('a.yd-rating-togglestatus').live('click', function(){
        var id = this.id.split('-')[3];
        $.post("/request_administration/toggleratingstatus", {
            ratingId : id
        }, function (data) {
            var status = 0;
            if (data.state) {
                status = 1;
            }
            $('#yd-rating-togglestatus-' + id).html('<img src="/media/images/yd-backend/online_status_' + status + '.png"/ alt="Status ändern">');
        }, "json");
        this.blur();
        return true;
    });

    // delete rating
    $('a.yd-rating-delete').live('click', function(){
        var id = this.id.split('-')[3];
        $.post("/request_administration/deleterating", {
            ratingId : id
        }, function (data) {
            if (data.state) {
                $('#yd-rating-container-' + id).html('<a href="#x" id="yd-rating-undelete-' + id +  '" class="yd-rating-undelete"><img src="/media/images/yd-backend/online_status_deleted.png"/ alt="Löschen"></a>');
            }
            else {
                alert(data.error);
            }
        }, "json");
        this.blur();
        return true;
    });

    // undelete rating
    $('a.yd-rating-undelete').live('click', function(){
        var id = this.id.split('-')[3];
        $.post("/request_administration/undeleterating", {
            ratingId : id
        }, function (data) {
            if (data.state) {
                $('#yd-rating-container-' + id).html('<a href="#x" id="yd-rating-togglestatus-' + id +  '" class="yd-rating-togglestatus"><img src="/media/images/yd-backend/online_status_0.png"/ alt="Status ändern"></a>&nbsp;&nbsp;&nbsp;<a href="#x" id="yd-rating-delete-' + id + '" class="yd-rating-delete"><img src="/media/images/yd-backend/del-cat.gif"/ alt="Löschen"></a>');
            }
            else {
                alert(data.error);
            }
        }, "json");
        this.blur();
        return true;
    });

    // set id for each row in ratings grid
    $('#yd-ratings-grid').each(function(){
        $('td.ratingId', this).each(function(){
            var id = parseInt($(this).html());
            $(this).parent().attr('id', 'yd-rating-row-' + id);
        });
    });

    /**-------------------------------------------------------------------------
     * Stats
     */
    $("span.yd-stats-benchmark").live("click", function(){
        $(this).hide().next().fadeIn().find(":text").val(this.innerHTML).focus();
    });
    $("form.yd-stats-benchmark").each(function(){
        var form = this;
        $(this).ajaxForm({
            success: function (data) {
                if (data.success) {
                    $(form).hide().prev().html(form.restaurants.value).fadeIn();
                }
            },
            dataType: "json"
        });
    });

    /**-------------------------------------------------------------------------
     * Todo
     */
    $('#yd-search-replace').ajaxForm({
        beforeSubmit: function () { 
            $('#yd-search-meals, #yd-search-categories').html('... SUCHE LÄUFT ..');
        },
        success: function (data) {
            $('#yd-search-meals, #yd-search-categories').html('');
            if (data.error) {
                notification('error', data.error);
            }
            if (data.success) {
                notification('success', data.success);
            }
            if (data.meals) {
                $.each(data.meals, function(index, meal){
                    $('#yd-search-meals').append(meal.name + " " + meal.description + "<br />");
                });
            }
            if (data.categories) {
                $.each(data.categories, function(index, category){
                    $('#yd-search-categories').append(category.name + " " + category.description + "<br />");
                });
            }
        },
        dataType: "json"
    });

   
    $('.yd-create-company').live('click',function(){
        var orderId = $(this).attr('id').split('-')[3];

        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'Anlegen' : true,
                'Abbrechen' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newcomp');
                $('#newcomp').load('/request_administration/createcompany/id/' + orderId);
            },
            submit: function(v,m,f) {
                if(!v){
                    return true;
                }
                
                $('#newcomp').html('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>');
                $('.promptfbuttons').hide();
                $.ajax({
                    type: "POST",
                    url: "/request_administration/createcompany/id/" + orderId,
                    data: (
                    {
                        'name' : f.name,
                        'street' : f.street,
                        'hausnr' : f.hausnr,
                        'etage' : f.etage,
                        'tel' : f.tel,
                        'plz' : f.plz
                    }
                    ),
                    success: function(msg){
                        $('.promptfbuttons').show();
                        $('#newcomp').html("<br />" + msg + "<br />");
                        $('#promptf_state0_buttonAnlegen').hide();
                        $('#promptf_state0_buttonAbbrechen').html('Seite neu laden');
                        $('#promptf_state0_buttonAbbrechen').live('click',function(){
                            location.reload();
                        });
                    }
                });

                return false;
            }
        });
        return false;
    });

    //provide download via link, each time, this value changes
    $('.yd-select-sent-bill').live('change',function(){
        var sent = $(this).val();
        var bill = $(this).attr('id').split('-')[4];
        $('#yd-download-selected-bill-' + bill).hide();
        $.ajax({
            type: "POST",
            url: "/request_administration/downloadsentbill/id/" + sent,
            success: function(file){
                $('#yd-download-selected-bill-' + bill).attr('href',file);
                $('#yd-download-selected-bill-' + bill).show();
            }
        });
    });
    
    $('.yd-mahnung').live('click',function(){
        var billingId = $(this).attr('id').split('-')[2];
        var bills = [];
        var steps = [];
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'Erstellen' : true,
                'Abbrechen' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','mahnung');
                $('#mahnung').load('/request_administration/mahnung/id/' + billingId);
            },
            submit: function(v,m,f) {
                if(!v){
                    return true;
                }
                
                //get
                $('.yd-select-sent-bill').each(function(){
                    bills.push(($(this).val()));
                });
                $('.yd-select-step-bill').each(function(){
                    steps.push(($(this).val()));
                });
                
                $('#mahnung').html('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>');
                $('.promptfbuttons').hide();
                $.ajax({
                    type: "POST",
                    url: "/request_administration/mahnung/",
                    data: (
                    {
                        'bills'     : bills,
                        'text'      : f.text,
                        'reminder'  : f.reminder,
                        'steps'      : steps,
                        'heading'   : f.heading
                    }
                    ),
                    success: function(msg){
                        $('#myd-create-user-emailahnung').html(msg);
                    }
                });

                return false;
            }
        });
        return false;
    });

    // http document root of website
    var httpaddress = $("#httproot").val();
    Date.format = 'dd.mm.yyyy';

    // even and odd rows for tables
    function evenodd(){
        $("table").find("tr:even").addClass("even");
        $("table").find("tr:odd").addClass("odd");
    }
    evenodd();

    // datepicker for graphical statistics start time
    if ( $('#yd-graphic-start-date').length > 0){
        initDatepicker('before', 'yd-graphic-start-date');
    }

    // datepicker for graphical statistics end time
    if ( $('#yd-graphic-end-date').length > 0){
        initDatepicker('before', 'yd-graphic-end-date');
    }

    // datepicker for statistics start time
    if ( $('#yd-statistics-start').length > 0){
        initDatepicker('beforeAndToday', 'yd-statistics-start');
    }

    // datepicker for statistics end time
    if ( $('#yd-statistics-end').length > 0){
        initDatepicker('beforeAndToday', 'yd-statistics-end');
    }

    // datepicker for billing asset creation start time
    if ( $('#yd-billingasset-create-start').length > 0){
        initDatepicker('full', 'yd-billingasset-create-start');
    }

    // datepicker for billing asset creation end time
    if ( $('#yd-billingasset-create-end').length > 0){
        initDatepicker('full', 'yd-billingasset-create-end');
    }

    // datepicker for next billing reservation start time
    if ( $('#yd-nextBilling-create-start').length > 0){
        initDatepicker('full', 'yd-nextBilling-create-start');
    }
    
    // datepicker for next billing reservation start time
    if ( $('#yd-canteen-dta-create-start').length > 0){
        initDatepicker('full', 'yd-canteen-dta-create-start');
    }

    // datepicker for next billing reservation start time
    if ( $('#yd-canteen-dta-create-end').length > 0){
        initDatepicker('full', 'yd-canteen-dta-create-end');
    }

    // datepicker for next billing reservation end time
    if ( $('#yd-nextBilling-create-end').length > 0){
        initDatepicker('full', 'yd-nextBilling-create-end');
    }

    // datepicker for restaurants per plz statistics
    if ( $('#yd-show-restaurants-stats-from').length > 0){
        initDatepicker('before', 'yd-show-restaurants-stats-from');
    }

    // datepicker for restaurants per plz statistics
    if ( $('#yd-show-restaurants-stats-until').length > 0){
        initDatepicker('full', 'yd-show-restaurants-stats-until');
    }


    // date- and timepicker an finish-pages
    if ( $('#yd-special-openings').length > 0){
        initDatepicker('now', 'yd-special-openings');
    }

    // date- and timepicker an holidays page
    if ( $('#yd-opening-holiday').length > 0){
        initDatepicker('now', 'yd-opening-holiday');
    }

    // date- and timepicker
    if ( $('#yd-date').length > 0){
        initDatepicker('full', 'yd-date');
    }

    // date- and timepicker
    if ( $('#yd-date-full-from').length > 0){
        initDatepicker('full', 'yd-date-full-from');
    }

    // date- and timepicker
    if ( $('#yd-date-full-until').length > 0){
        initDatepicker('full', 'yd-date-full-until');
    }

    // date- and timepicker an finish-pages
    if ( $('#yd-salesperson-worktimes-openings').length > 0){
        initDatepicker('before', 'yd-salesperson-worktimes-openings');
    }

    if($('#yd-admin-start-dateshort').length > 0){
        initDatepicker('full', 'yd-admin-start-dateshort');
        initDatepicker('full', 'yd-admin-end-dateshort');
    }

    // datepicker for additional comission data start time
    if ( $('#yd-additional-comission-start').length > 0){
        initDatepicker('full', 'yd-additional-comission-start');
    }

    // datepicker for additional comission data end time
    if ( $('#yd-additional-comission-end').length > 0){
        initDatepicker('full', 'yd-additional-comission-end');
    }

    // datepicker for restaurant offline status end time
    if ( $('#yd-offline-status-until').length > 0){
        initDatepicker('now', 'yd-offline-status-until');
    }
    
    // datepicker for the order-grid-search
    if ($('#filter_Lieferzeitgrid').length > 0) {
        initDatepicker('full', 'filter_Lieferzeitgrid');
    }
        
    // datepicker for the order-grid-search
    if ($('#filter_Einganggrid').length > 0) {
        initDatepicker('full', 'filter_Einganggrid');
    }
        
    // datepicker for the ratings-grid-search
    if ($('#filter_Amratings').length > 0) {
        initDatepicker('beforeAndToday', 'filter_Amratings');
    }
    
    /**
     *  datepicker for support tracking
     *  @modified daniel
     *  @ since 10.10.2011
     */    
    if ( $('#yd-support-tracking-from').length > 0){    
        initDatepicker('beforeAndToday', 'yd-support-tracking-from');         
    }
    
    if ( $('#yd-support-tracking-until').length > 0){
        initDatepicker('beforeAndToday', 'yd-support-tracking-until');
    }
    
    if ( $('#yd-transaction-start').length > 0){
        initDatepicker('full', 'yd-transaction-start');
    }

    // hide password field if the email is found in the admin_access_users table
    $('#yd-create-salesperson-email').live('blur',function(){
        email = $('#yd-create-salesperson-email').val();

        $.ajax({
            type: "POST",
            url : '/administration_salesperson/testmail',
            data: (
            {
                'email' : email
            }
            ),
            success: function(result){
                if (result == 0){
                    $('#yd-create-salesperson-email-admin-status').html('');
                    $('#yd-create-salesperson-password').show();
                    $('#yd-create-salesperson-button').show();
                }
                else if (result == 1){
                    $('#yd-create-salesperson-email-admin-status').html('Der Benutzer ist bereits als Vertriebler registriert');
                    $('#yd-create-salesperson-password').hide();
                    $('#yd-create-salesperson-button').hide();
                }
                else {
                    $('#yd-create-salesperson-email-admin-status').html('Der Benutzer hat bereits Zugang zum Admin-backend, er wird nur als Vertriebler angelegt.');
                    $('#yd-create-salesperson-password').hide();
                    $('#yd-create-salesperson-button').show();
                }
            }
        });
    });

    // show save link when comission in salesperson-company assiociation changed
    $('.yd-salesperson-company-assoc').live('change',function(){
        var id = $(this).attr('id').split('-')[2];
        $('#yd-savecomission-row-' + id).html('<a href="#x" class="yd-save-salesperson-company-comission" id="yd-savecomission-link-' + id + '">speichern</a>');
    });

    // save new comission in salesperson-company association
    $('.yd-save-salesperson-company-comission').live('click',function(){
        var assocId = $(this).attr('id').split('-')[3];
        var comission = $('#yd-comission-' + assocId).val();

        $.ajax({
            type: "POST",
            url : '/administration_salesperson/editcompanycomission',
            data: (
            {
                'assocId' : assocId,
                'comission' : comission
            }
            ),
            success: function(error){
                if (error != 'ok') {
                    alert(error);
                }
                $('#yd-savecomission-row-' + assocId).html('<font color="#aaa">speichern</font>');
            }
        });
    });

    // show notification if this email is already in the contact table and not marked as deleted
    $('#yd-create-contact-email').live('blur',function(){
        email = $('#yd-create-contact-email').val();

        $.ajax({
            type: "POST",
            url : '/request_administration/testmail',
            data: (
            {
                'email' : email
            }
            ),
            success: function(data){
                if (data.status == 2){
                    $('#yd-create-contact-email-status').html('');
                    $('#yd-create-contact-button').show();
                }
                else if (data.status == 1){
                    $('#yd-create-contact-email-status').html('Unter der Adresse ist bereits ein Kontakt registriert:<br>' + data.code);
                    $('#yd-create-contact-button').hide();
                }
                else if (data.status == 0){
                    $('#yd-create-contact-email-status').html('Error! No response from server after testing the email');
                    $('#yd-create-contact-button').hide();
                }
            },
            dataType: 'json'
        });
    });

    // show notification if this email is already in the customers table and not marked as deleted
    $('#yd-create-user-email').live('blur',function(){
        email = $('#yd-create-user-email').val();

        $.ajax({
            type: "POST",
            url : '/administration_user/testmail',
            data: (
            {
                'email' : email
            }
            ),
            success: function(result){
                if (result == 0){
                    $('#yd-create-user-email-status').html('');
                    $('#yd-create-user-button').show();
                }
                else {
                    $('#yd-create-user-email-status').html('Unter der Adresse ist bereits ein Benutzer registriert:<br>' + result);
                    $('#yd-create-user-button').hide();
                }
            }
        });
    });

    // set if registered/edited columns should be shown and change the link text
    $('.yd-show_edit_time-link').live('click',function(){
        var type = $(this).attr('id');

        if (type == '') {
            return;
        }

        var showedittime = '';
        if (type == 'yd-settings-showlink') {
            showedittime = 'show';
            $('#yd-show_edit_time').html('<a href="#" class="yd-show_edit_time-link" id="yd-settings-hidelink">Registriert/geändert Spalten verbergen</a>');
        }
        else if (type == 'yd-settings-hidelink') {
            showedittime = 'hide';
            $('#yd-show_edit_time').html('<a href="#" class="yd-show_edit_time-link" id="yd-settings-showlink">Registriert/geändert Spalten anzeigen</a>');
        }

        $('#yd-settings-status').html('Einstellungen wurden gespeichert!');
        setTimeout(function(){
            $('#yd-settings-status').html('');
        }, 2000);

        $.ajax({
            type: "POST",
            url : '/administration/settings',
            data: (
            {
                'showedittime' : showedittime
            }
            ),
            success: function(){
            }
        });
    });

    $('.toggleOptions').click(function() {
        $(this).next().toggle();
        return false;
    });

    //hide and show bill contact
    $('#yd-bill-as-contact').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-other-bill').hide();
        }
        else{
            if ( $('#yd-bill-prename').val() == ""){
                $('#yd-remove_billcontact').hide();
            }

            $('#yd-other-bill').show();
        }
    });

    //save blacklist
    $('.yd-save-blacklist').live('click',function(){
        var blacklist = $('#blacklist').val();

        $.ajax({
            type: "POST",
            url : '/request_administration/blacklist',
            data: {
                'blacklist': blacklist
            },
            success: function(){
                notification('success', 'Blacklist gespeichert');
            }
        });
    });

    //change type of salers - call center or external
    $('#yd-salesperson-type').live('change',function(){
        if($(this).val() == "1"){
            $('#yd-salesperson-salary-type').html('&euro; pro Stunde');
        }
        else {
            $('#yd-salesperson-salary-type').html('&euro; pro Vertrag');
        }
    });

    //check if restaurant called or contract was made by salesperson
    $('#yd-restaurant-called').live('change',function(){
        if ( $('#yd-restaurant-called').is(':checked') ){
            $('#yd-salesperson_dropdown').hide();
        }
        else {
            $('#yd-salesperson_dropdown').show();
        }
    });

    $('.yd-toggle-support').live('change', function(){
        var support = $(this).attr('id').split('-')[3];
        $.ajax({
            type: "POST",
            url : '/request_administration/support',
            data: (
            {
                'support'    : support
            }
            )
        });
    });

    $('.yd-remove-support').live('click', function(){
        var id = $(this).attr('id').split('-')[3];
        $.ajax({
            type: "POST",
            url : '/request_administration/removesupport',
            data: (
            {
                'support'    : id
            }
            ),
            success: function(html){
                if ( html == 1 ){
                    $('#yd-support-'+id).remove();
                }
            }
        });
    });

    $('#yd-add-support-number').live('click', function(){
        var support = $('#yd-add-support-number-text').val();
        var name = $('#yd-add-support-name-text').val();
        $.ajax({
            type: "POST",
            url : '/request_administration/addsupport',
            data: (
            {
                'support'    : support,
                'name'       : name
            }
            ),
            success: function(html){
                $('#yd-support').append(html);
            }
        });
    });

    //check/uncheck all checkboxes
    $('.yd_editgroup_selectall').live('change',function(){
        if ( $(this).is(':checked') ){
            $('.yd_resource').prop('checked', true);
            $('.yd_editgroup_selectall').prop('checked', true);
        }
        else {
            $('.yd_resource').prop('checked', false);
            $('.yd_editgroup_selectall').prop('checked', false);
        }
    });

    //hide and show forms for contact data
    $('#yd-select_contact').live('change',function(){
        if($(this).val()!="-1"){
            $('#yd-create-contact').hide();
            $('#yd-show-contact').hide();
        }
        else {
            $('#yd-create-contact').show();
        }
    });
    
    //hide and show forms for franchise data
    $('#yd-select_franchise').live('change',function(){
        if($(this).val()!="-1"){
            $('#yd-create-franchise').hide();
        }
        else {
            $('#yd-create-franchise').show();
        }
    });

    //hide and show offline states
    $('#yd-status-changed').live('change',function(){
        if($(this).val() == "1"){
            $('#yd-offlinestatus').hide();
            $('.yd-save-restaurant-button').show();
            $('#yd-offline_change_reason').hide();
            $('#yd-offline-change-reason-text').val('');
            $('.yd-status-changed-missing-reason').hide();
            $('#yd-offlinestatus-until').hide();
        }
        else {
            $('#yd-offlinestatus').show();
            $("#yd-offline-reason").val(23);
            $('#yd-offline_change_reason').show();
            $('.yd-save-restaurant-button').hide();
            $('.yd-status-changed-missing-reason').show();
        }
    });

    //show save button if some text was entered in offline reason textarea
    $('#yd-offline-change-reason-text').live('keyup',function(){
        var reason = $(this).val();

        //trim string, so white spaces are not included
        reason  = reason.replace(/^\s\s*/, '').replace(/\s\s*$/, '');

        if (reason.length > 0) {
            $('.yd-status-changed-missing-reason').hide();
            $('.yd-save-restaurant-button').show();
        }
        else {
            $('.yd-status-changed-missing-reason').show();
            $('.yd-save-restaurant-button').hide();
        }
    });


    //change activated status to "active" when offline reason is set to online
    $('#yd-offline-reason').live('change',function(){
        var newStatus = $(this).val();
        
        if(newStatus == "0"){
            $("#yd-status-changed").val(1);
            $('#yd-offlinestatus').hide();
            $('.yd-save-restaurant-button').show();
            $('.yd-status-changed-missing-reason').hide();
            $('#yd-offline_change_reason').hide();
        }
        else {
            $('#yd-offline_change_reason').show();
            $('.yd-save-restaurant-button').hide();
            $('.yd-status-changed-missing-reason').show();

            if ( (newStatus == "5") || (newStatus == "7") || (newStatus == "12") || (newStatus == "14")) {
                $('#yd-offlinestatus-until').show();
            }
            else {
                $('#yd-offlinestatus-until').hide();
            }

        }
    });

    //hide and show forms for billing contact data
    $('#yd-select_billcontact').live('change',function(){
        $('#yd-remove_billcontact').hide();
        if($(this).val()!="-1"){
            $('#yd-create-billcontact').hide();
            $('#yd-show-billcontact').hide();
        }
        else {
            $('#yd-create-billcontact').show();
        }
    });

    $('.yd-duplicate').live('change',function(){
        if ( $('#yd-bill-as-contact').is(':checked') ){
            var elems = $(this).attr('id').split('-');
            var copy = 'yd-bill-'+elems[2];
            $('#'+copy).val($(this).val());
        }
    });

    //show popup with discount codes
    $('.yd-show-discount-codes').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        
        $.ajax({
            type: "POST",
            url : '/request_administration/getcodescount',
            data: (
            {
                'id' : id
            }
            ),
            success: function(data){
                if (parseInt(data.count) < 101) {
                    $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
                        prefix: 'promptf',
                        buttons: {
                            'OK' : true
                        },
                        loaded: function() {
                            $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','discount');
                            $('#discount').load('/request_administration/discount/id/'+id);
                        }
                    });
                }
                else {
                    alert ('Die Rabattaktion hat ' + data.count + ' Gutscheine. Das ist zu viel und kann nicht angezeigt werden.');
                }
            }, 
            dataType: 'json'
        });        
    });
    
    
    //show popup with discount registration codes
    $('.yd-show-verification-codes').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        
        $.ajax({
            type: "POST",
            url : '/request_administration/getregistrationcodescount',
            data: (
            {
                'id' : id
            }
            ),
            success: function(data){
                if (parseInt(data.count) < 101) {
                    $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
                        prefix: 'promptf',
                        buttons: {
                            'OK' : true
                        },
                        loaded: function() {
                            $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','discount');
                            $('#discount').load('/request_administration/registrationcodes/id/' + id);
                        }
                    });
                }
                else {
                    alert ('Die Rabattaktion hat ' + data.count + ' Codes.  Das ist zu viel und kann nicht angezeigt werden.');
                }
            }, 
            dataType: 'json'
        });        
    });    

    //display or hide company values if we want to associate directly
    $('#yd-assoc-comp').live('change',function(){
        var id = $(this).val();
        if ( id > 0 ){
            $('#assco-new-comp').hide();
        }
        else{
            $('#assco-new-comp').show();
        }
    });

    $('.yd-check-directlink').live('blur',function(){
        $('#directLink-msg').html('');
        var link = $(this).val();
        var data = $(this).attr('id').split('-');
        var id = data[1];
        var idPart = data[0];
        $.ajax({
            url:'/request_administration/checkdirectlink/',
            type: 'POST',
            data: ({
                'link' : link,
                'id'   : id
            }),
            success: function(data){
                $('#'+idPart+'-msg').html(data);
            }
        });
    });

    // show prompt lightbox
    $('.yd-view-prompt').live('click', function(){
        var id = this.id.split('-')[3];
        // lightbox is already open
        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id', 'orderedit');
        $('#orderedit').load('/request_administration/prompt/id/' + id, function(){
            $("form", this).ajaxForm({
                target: "#orderedit"
            });
        });
    });

    //show link to selected discount in company and user edit page
    $('#yd-discount-dropdown').live('change',function(){
        var id = $(this).val();
        var text = $('#yd-discount-dropdown :selected').text();
        
        if (id == -1) {
            $('#yd-selected-discount-link').html('');
        }
        else {
            $('#yd-selected-discount-link').html('<a href="/administration_discount/discountbycode/id/' + id + '">' + text + '</a>');
        }
    });

    // send bill
    $('form.yd-send-bill').ajaxForm({
        beforeSubmit : function(arr, $form, options){
            $form.hide();
        },
        success: function (data, status, xhr, $form) {
            $form.show();
            
            if (data.success) {
                notification('success', data.success);
                $('#yd-bill-status-' + data.id).val(data.status);
            }
            
            if (data.error) {
                notification('error', data.error);
            }
            
        },
        dataType: 'json'
    });

    $('#yd-canteen-dta').live('click',function(){
        var from = $('#yd-canteen-dta-create-start').val();
        var until = $('#yd-canteen-dta-create-end').val();
        location.href = '/administration_billing/interval/from/' + from + '/until/' + until + '/type/dat_canteen';
    });

    $('#yd-reserve-number').live('click',function(){
        var type = $('#nextBilling-type').val();

        var refId = 0;

        if (type == 'company') {
            refId = $('#nextBilling-companies-list').val();
        }
        else if (type == 'rest') {
            refId = $('#nextBilling-services-list').val();
        }
        else {
            refId = $('#nextBilling-couriers-list').val();
        }
        
        var from = $('#yd-nextBilling-create-start').val();
        var until = $('#yd-nextBilling-create-end').val();
            
        $.ajax({
            type: "POST",
            url : '/request_administration/reservebillingnumber',
            data: (
            {
                'type' : type,
                'refId' : refId,
                'from' : from,
                'until' : until
            }
            ),
            success: function(response){
                if (response) {
                    var generatedBillingNr = response.split(':')[0];
                    var infoNrs = response.split(':')[1];

                    var nr = infoNrs.split('-')[0];
                    var nextNr = infoNrs.split('-')[1];
                    $('#yd-reserve').append("<b>" + nr + "</b> wurde erfolgreich reserviert<br /> Rechnung: " + generatedBillingNr + "<br /><br />");
                    $('#nextBillingNumber').html(nextNr);
                }
            }
        });
    });

    //change the list of billing owner to restaurants/companextBilling-companies-listny, depending on selected option
    $('#nextBilling-type').live('change',function(){
        if($(this).val() == 'company'){
            $('#yd-nextBilling-owner-type').html('Firma:');
            $('#yd-nextBilling-owner-list-services').hide();
            $('#yd-nextBilling-owner-list-couriers').hide();
            $('#yd-nextBilling-owner-list-companies').show();
        }
        else if($(this).val() == 'rest'){
            $('#yd-nextBilling-owner-type').html('Dienstleister:');
            $('#yd-nextBilling-owner-list-services').show();
            $('#yd-nextBilling-owner-list-couriers').hide();
            $('#yd-nextBilling-owner-list-companies').hide();
        }
        else {
            $('#yd-nextBilling-owner-type').html('Kurierdienst:');
            $('#yd-nextBilling-owner-list-services').hide();
            $('#yd-nextBilling-owner-list-couriers').show();
            $('#yd-nextBilling-owner-list-companies').hide();
        }
    });

 
    //close the discount code edit lightbox
    $('.yd-rabattcode-cancel').live('click',function(){
        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','rabattcodeedit');
        jQuery.prompt.close();
    });

    //restore the content of the rabatt code edit lightbox
    $('.yd-rabattcode-back').live('click',function(){
        var id = $(this).attr('id').split('-')[3];
        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','rabattcodeedit');
        $('#rabattcodeedit').load('/request_administration/rabattcodeedit/id/'+id);
    });

    $('.startTimeS, .endTimeS').datepicker({
        startDate: '01/01/2009',
        endDate: '',
        clickInput: true,
        createButton: false
    });
    
    $(".startTimeS").bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(".endTimeS").dpSetStartDate(d.addDays(0).asString());
            }
        }
        );
    $(".endTimeS").bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(".startTimeS").dpSetEndDate(d.addDays(0).asString());
            }
        }
        );

    // refresh charts
    $('.refreshChart').click(function() {

        var form = $(this).parent().parent();
        var container = form.parent().parent();
        var width = container.width();

        container.find('.img').html('').image(
            httpaddress +
            'request/getchart/class/' +
            form.find('select[name=class]').val() +
            '/width/' +
            width +
            '/start/' +
            form.find('input[name="startTimeD]').val() + '-' + form.find('input[name=startTimeT"]').val() +
            '/end/' +
            form.find('input[name="endTimeD]').val() + '-' + form.find('input[name=endTimeT"]').val(),

            function() {
            // lade animation
            }
            );
        return false;
    });


    //resend orderfax
    $('.yd-resend-orderfax').live('click',function(){
        var value = confirm('Wollen Sie diese Bestellung wirklich erneut senden?');
        if (!value) {
            return false;
        }
        $(this).parent().html('... wird versendet ...');
        $.ajax({
            type: "POST",
            url : '/request_administration/resendorder',
            data : {
                'id'   : $(this).attr('id').split('-')[3]
            },
            success : function(){
                notification('success','Fax erfolgreich versendet');
            }
        });
    });
    
    if($('#restaurant-start-compare-date').length > 0){
        initDatepicker('before', 'restaurant-start-compare-date');
    }
    if($('#restaurant-end-compare-date').length > 0){
        initDatepicker('before', 'restaurant-end-compare-date');
    }

    $('#restaurant-start-compare-date').live('change',function(){
        $('#growth-sort-by').fadeIn(1000);
    });
    
    //show the dashboard data async
    //$('#dashboard').load('/request_administration/dashboard');

    //show the detailed info data async
    $('#detailedinfo').load('/request_administration/detailedinfo');

    $('#growth-sort-by').hide();
    //show the comparison statistic ( top 10 )
    $('#load').hide();
    $('#topplz').hide();
    $('#topcity').hide();
    $('#showstats').click(function(){
        if(status == 1){
            $('#topplz').fadeOut();
            $('#topcity').fadeOut();
            $('#showstats').val('Show Statistic');
            status = 0;
        }
        else if(status != 1){
            $('#topplz').show();
            $('#topcity').show();
            $('#topplz').load('/administration_service_benchmark/topplz');
            $('#topcity').load('/administration_service_benchmark/topcity');
            $('#showstats').val('Hide Statistic');
            status = 1;
        }
    });

    //csv export based on plz
    $('.csvexport').click(function(){
        var mode = $(this).attr('id').split('+');
        var plz = [];
        var city = [];
        switch(mode[2]){
            case "plz":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    plz.push(this.value);
                });
                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/plz",
                    data: ({
                        'plz'      : plz
                    }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
        
            case "city":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    city.push(this.value);

                });

                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/city",
                    data: ({
                        'city'      : city
                    }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
        
            case "all":
                $('#load').show();
                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/all",
                    data: ({

                        }),
                    success : function(){

                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
           
            case "topcity":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    city.push(this.value);

                });

                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/topcity",
                    data: ({
                        'city'      : city
                    }),
                    success : function(){

                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
            case "topplz":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    plz.push(this.value);

                });

                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/topplz",
                    data: ({
                        'plz'      : plz
                    }),
                    success : function(){

                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
            case "topall":
                $('#load').show();
                $.ajax({
                    type: "POST",
                    url: "/administration_service_benchmark/export/type/topall",
                    data: ({

                        }),
                    success : function(){

                        $('#load').hide();
                        location.reload();
                    }
                });
                break;

            default:
                break;
        }
      
    });
    
    //lightbox prompt to create new budget
    $(".new-budget").bind('click',function() {
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'JETZT BEWERTEN' : true,
                'ABBRECHEN' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newbudget');
                $('#newbudget').load('/administration_import_company/budget');
            },
            submit: function(v,m,f) {
                if ( !v ){
                    return true;
                }
                $('#errors').hide();
                var error = false;
                if ( f.budgetname == "" ){
                    $('input[name="budgetname"]').css('border','2px solid red');
                    error = true;
                }
               

                if ( error ){
                    $('#errors').show();
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "/administration_import_company/budget",
                    data: 
                    {
                        'budgetname' : f.budgetname
                            
                    },
                    
                    success : function(){
                        notification('success','New Budge Group Added');
                        $('.budget').append('<option value="'+f.budgetname+'" id="new">'+f.budgetname+'</option>');
                        var sel = document.getElementById('budget-'+id);
                        var x = (sel.length-1);
                        sel.selectedIndex = x;
                        var total = $(".allbudget").attr('id');
                        $('.all-budget').append('<option value="'+f.budgetname+'" onclick="selectAll('+x+','+total+',1)">'+f.budgetname+'</option>');
                               
                    }
                });
                
            }
        });
    });

    //lightbox create new costcenter
    $(".new-kosten").bind('click',function() {
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'JETZT BEWERTEN' : true,
                'ABBRECHEN' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newkosten');
                $('#newkosten').load('/administration_import_company/costcenter');
            },
            submit: function(v, m, f) {
                if ( !v ){
                    return true;
                }
                $('#errors').hide();
                var error = false;
                if ( f.kostenstellename == "" ){
                    $('input[name="kostenstellename"]').css('border','2px solid red');
                    error = true;
                }

                if ( f.kostenstelleidnummer == "" ){
                    $('input[name="kostenstelleidnummer"]').css('border','2px solid red');
                    error = true;
                }

                if ( error ){
                    $('#errors').show();
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "/administration_import_company/costcenter",
                    data:
                    {
                        'kostenstellename' : f.kostenstellename,
                        'kostenstelleidnummer' : f.kostenstelleidnummer
                    },

                    success : function(){
                        notification('success','New Costcenter Added');
                        $('.kostenstelle').append('<option value="'+f.kostenstellename+'" >'+f.kostenstellename+'</option>');
                        var sel = document.getElementById('kosten-'+id);
                        var x = (sel.length-1);
                        sel.selectedIndex = x;
                        var total = $(".allkosten").attr('id');
                        $('.all-kostenstelle').append('<option value="'+f.kostenstellename+'" onclick="selectAll('+x+','+total+',2)">'+f.kostenstellename+'</option>');


                    }
                });
                return true;
            }
        });
    });

    
    //lightbox to create new budget and auto assign to all
    $(".allbudget").click(function() {
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'JETZT BEWERTEN' : true,
                'ABBRECHEN' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newbudget');
                $('#newbudget').load('/administration_import_company/budget');
            },
            submit: function(v,m,f) {
                if ( !v ){
                    return true;
                }
                $('#errors').hide();
                var error = false;
                if ( f.budgetname == "" ){
                    $('input[name="budgetname"]').css('border','2px solid red');
                    error = true;
                }

                if ( error ){
                    $('#errors').show();
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "/administration_import_company/budget",
                    data:
                    {
                        'budgetname' : f.budgetname
                    },

                    success : function(){
                        notification('success','New Budge Group Added');
                       
                        $('.budget').append('<option value="'+f.budgetname+'" selected >'+f.budgetname+'</option>');
                        var sel = document.getElementById('budgetAll');
                        var x =(sel.length);
                        $('.all-budget').append('<option value="'+f.budgetname+'" onclick="selectAll('+x+','+id+',1)" selected >'+f.budgetname+'</option>');
                    }
                });
                return true;
            }
        });
    });


    //activate/deactivate nofitication fields in restaurnat edit
    $('#yd-service-noNotification').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-service-notifyOpen').hide();
            $('#yd-service-notifyPayed').hide();
        }
        else{
            $('#yd-service-notifyOpen').show();
            $('#yd-service-notifyPayed').show();
        }
    });

    //save admin group description
    $('.yd-admingroup-savelink').live('click',function(){
        var id = $(this).attr('id').split('-')[3];
        var description = $('#yd-admingroup-desc-' + id).val();

        $.ajax({
            type: "POST",
            url: "/administration_adminrights/savegroupdesc",
            data:
            {
                'id' : id,
                'description' : description
            },

            success : function(result){
                if (result != 'ok') {
                    alert(result);
                }
                $('#yd-save-' + id).html('<font color="#999999">Speichern</font>');
            }
        });
        return true;
    });

    //activate "save" link in admin resources list when group name changed
    $('.yd-admingroup_desc').live('change',function(){
        var id = $(this).attr('id').split('-')[3];
        $('#yd-save-' + id).html('<b><a href="#' + id + '" class="yd-admingroup-savelink" id="yd-admingroup-savelink-' + id + '">Speichern</a></b>');
    });

    //lightbox create new costcenter
    $(".allkosten").bind('click',function() {
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'JETZT BEWERTEN' : true,
                'ABBRECHEN' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newkosten');
                $('#newkosten').load('/administration_import_company/costcenter');
            },
            submit: function(v,m,f) {
                if ( !v ){
                    return true;
                }
                $('#errors').hide();
                var error = false;
                if ( f.kostenstellename == "" ){
                    $('input[name="kostenstellename"]').css('border','2px solid red');
                    error = true;
                }
                if ( f.kostenstelleidnummer == "" ){
                    $('input[name="kostenstelleidnummer"]').css('border','2px solid red');
                    error = true;
                }

                if ( error ){
                    $('#errors').show();
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "/administration_import_company/costcenter",
                    data:
                    {
                        'kostenstellename' : f.kostenstellename,
                        'kostenstelleidnummer' : f.kostenstelleidnummer
                    },

                    success : function(){
                        notification('success','New Costcenter Added');
                        $('.kostenstelle').append('<option value="'+f.kostenstellename+'" selected>'+f.kostenstellename+'</option>');
                        var sel = document.getElementById('kostenstelleAll');
                        var x =(sel.length);
                        $('.all-kostenstelle').append('<option value="'+f.kostenstellename+'" onclick="selectAll('+x+','+id+',2)" selected>'+f.kostenstellename+'</option>');
                    }
                });
                return true;
            }
        });
    });

    //color orders in grid
    if ( $('#yd-order-grid').length > 0 ){
        $('.status').each(function(){
            if ( $(this).html() == "Prepayment" ){
                $(this).parent().css('background','#bbb');
            }
            if ( $(this).html() == "Fake" ){
                $(this).parent().css('background','#abc');
            }
            if ( $(this).html() == "Blacklist" ){
                $(this).parent().css('background','#123');
            }
            if ( $(this).html() == "Error" || $(this).html() == "Rejected by Restaurant" ){
                $(this).parent().css('background','#ffc');
            }
            if ( $(this).html() == "Affirmed" || $(this).html() == "Delivered" ){
                $(this).parent().css('background','#d5ffce');
            }
            if ( $(this).html() == "Storno" ){
                $(this).parent().css('background','#ffcece');
            }
            if ( $(this).html() == "Not affirmed on billing" ){
                $(this).parent().css('background','orange');
            }
            if ( $(this).html() == "Not affirmed" ){
                $(this).parent().css('background','#fff');
            }
        });
    }

    /**
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         **/
    $('.yd-canteenorder-detail').live('click', function(){
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'Abbrechen' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','canteenorderdetails');
                $('#canteenorderdetails').load('/request_administration/canteenorderdetails/id/'+id);
            },
            submit: function(t) {
                return true;
            }
        });
    });
    
    //lightbox test
    $('#yd-test').live('click',function(){
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'fortsetzen' : true,
                'zurück zur Auswahl' : false
            },
            loaded: function() {
            },
            submit: function(t) {
                return true;
            }
        });
    });
    
    //get list of compare restaurant based on clicked id
    $(".diff").click(function() {
        var id = $(this).attr('id').split('+');
        id[2] = escape(id[2]);
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','diff');
                $('#diff').load('/administration_service_benchmark/detail/'+id[1]+'/'+id[2]);
            }
        });
    });

    //get list of excluded restaurant
    $(".excl").click(function() {
        var id = $(this).attr('id').split('+');
        id[2] = escape(id[2]);
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','excl');
                $('#excl').load('/administration_service_benchmark/excludelist/'+id[1]+'/'+id[2]);
            }
        });
    });

    //get list of restaurant from yourdelivery based on clicked id
    $(".resto").click(function() {
        var id = $(this).attr('id').split('+');
        id[2] = escape(id[2]);
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','resto');
                $('#resto').load('/administration_service_benchmark/restoyd/'+id[1]+'/'+id[2]);
            }
        });
    });

    //get list of restaurant from pizza.de based on clicked id
    $(".pizza").click(function() {
        var id = $(this).attr('id').split('+');
        id[2] = escape(id[2]);
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','pizza');
                $('#pizza').load('/administration_service_benchmark/restopizzade/'+id[1]+'/'+id[2]);
            }
        });
    });
    
    //get list of all restaurant based on clicked id
    $(".all").click(function() {
        var id = $(this).attr('id').split('+');
        id[2] = escape(id[2]);
        if(id[1] == 'toplistcity' || id[1] == 'toplistresto'){
            $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
                prefix: 'promptf',
                buttons: {
                    'OK' : false
                },
                loaded: function() {
                    $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','all');
                    $('#all').load('/administration_service_benchmark/all/'+id[1]+'/'+id[2]);
                }
            });
        }
        else if(id[1] == 'city' || id[1] == 'plz'){
            $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
                prefix: 'promptf',
                buttons: {
                    'OK' : false
                },
                loaded: function() {
                    $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','all');
                    $('#all').load('/administration_service_benchmark/all/'+id[1]+'/'+id[2]);
                }
            });
        }
    });
    //get the newest resto list which got from pizza.de
    $(".newresto").click(function() {
        var id = $(this).attr('id').split('+');
          
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','all');
                $('#all').load('/administration_service_benchmark/restopizzade/'+id[0]+'/'+id[1]);
            }
        });
    });
    
    //show restaurant for google export
    $(".googlelist").click(function() {
        var id = $(this).attr('id').split('+');
        id[1] = escape(id[1]);
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','all');
                $('#all').load('/administration_export_restaurant/showall/'+id[0]+'/'+id[1]);
            }
        });
    });

    //export restaurant for google LBC
    $('.google_export').click(function(){
        var mode = $(this).attr('id').split('+');
        var plz = [];
        var city = [];
        switch(mode[1]){

            case "city":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    city.push(this.value);
                });
            
                $.ajax({
                    type: "POST",
                    url: "/administration_export_restaurant/start/type/city/mode/lbc",
                    data: ({
                        'city'      : city
                    }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;

            case "all":
                $('#load').show();
                $.ajax({
                    type: "POST",
                    url: "/administration_export_restaurant/start/type/all/mode/lbc",
                    data: ({
                        }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
            default:
                break;
        }

    });

    //export restaurant for SEM
    $('.sem_export').click(function(){
        var mode = $(this).attr('id').split('+');
        var plz = [];
        var city = [];
        switch(mode[1]){

            case "city":
                $('#load').show();
                $('input[type="checkbox"]:checked').each(function(){
                    city.push(this.value);
                });

                $.ajax({
                    type: "POST",
                    url: "/administration_export_restaurant/start/type/city/mode/sem",
                    data: ({
                        'city'      : city
                    }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;

            case "all":
                $('#load').show();
                $.ajax({
                    type: "POST",
                    url: "/administration_export_restaurant/start/type/all/mode/sem",
                    data: ({
                        }),
                    success : function(){
                        $('#load').hide();
                        location.reload();
                    }
                });
                break;
            default:
                break;
        }

    });
    //export all pizza.de data 
    $('#all_pizza_data').click(function(){
        $('#load').show();
        $.ajax({
            type: "POST",
            url: "/administration_crawler/exportall",
            data: ({
                }),
            success : function(){
                $('#load').hide();
                location.reload();
            }
        });
    });
    //export pizza.de data based on given date
    $('#download_crawler').click(function(){
        var listdate = [];
        $('#load').show();
        $('input[type="checkbox"]:checked').each(function(){
            listdate.push(this.value);
        });
     
        $.ajax({
            type: "POST",
            url : '/administration_crawler/export',
            data: (
            {
                'listdate'    : listdate
            }
            ),
            success : function(){
                $('#load').hide();
                location.reload();
            }
        });
    });
    //show list of restaurant based on clicked date
    $('.crawler').click(function(){
        var id = $(this).attr('id').split('+');
        
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            scroll: true,
            buttons: {
                'Close' : false
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','crawlerData');
                $('#crawlerData').load('/administration_crawler/detail/datum/'+id[1]);
            }

        });
        

    });
    //auto check checkbox
    $('#checkAllAuto').click(function(){
        $("INPUT[type='checkbox']").attr('checked', $('#checkAllAuto').is(':checked'));
    });
    
    $('.deletecatpic').live('click', function(){
        var value = confirm('Wollen Sie dieses Bild wirklich löschen?');
        
        if (!value) {
            return false;
        }

        var cat_id = $('.yd-picture-category').attr('id');
        var pic = $(this).attr('id');
        $.ajax({
            type: "POST",
            url : '/request_administration/deletecatpic',
            data: (
            {
                'pic'   : pic,
                'id'    : cat_id
            }
            ),
            success: function(html){
                if ( html == 1 ){
                    notification('success', 'Bild erfogreich entfernt');
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                    return true;
                }
                else{
                    notification('error', 'Bild konnte nicht entfernt werden');
                    return false;
                }
                
            }
        });

        return true;
    });
    
    $('.yd-assign-picture-cat').live('click', function(){
        var restId = $('.yd-assign-picture-cat-restid').val();
        if(restId==''){
            notification('warn', 'Bitte wählen Sie einen Dienstleister aus');
            return false;
        }
        location.href = '/administration_service_edit/piccategories/id/'+restId;
        return true;
    });

    //append selected restaurant to list and show date interval 
    $('.yd-company-bill').click(function(){
        var id = $(this).val().split('-');
        var sel = document.getElementById('company-select-box');
        $('.yd-company-bill-list').append('<label id="company-'+id[0]+'" class="company-bill-lists" title="'+id[0]+'">'+id[1]+'&nbsp;<span id="company-'+id[0]+'" class="del-bill-list" style="cursor:pointer"><img src="/media/images/yd-backend/del-cat.gif" alt="Löschen" /></span><br /></label>');
        sel.selectedIndex=0;
        $('#select-bill-interval-date').fadeIn(1000);
    });

    //append selected restaurant to list and show date interval
    $('.yd-restaurant-bill').click(function(){
        var id = $(this).val().split('-');
        var sel = document.getElementById('restaurant-select-box');
        $('.yd-restaurant-bill-list').append('<label id="restaurant-'+id[0]+'" class="restaurant-bill-lists" title="'+id[0]+'">'+id[1]+'&nbsp;<span id="restaurant-'+id[0]+'" class="del-bill-list" style="cursor:pointer"><img src="/media/images/yd-backend/del-cat.gif" alt="Löschen" /></span><br /></label>');
        sel.selectedIndex=0;
        $('#select-bill-interval-date').fadeIn(1000);

    });

    //remove an element if user click on delete
    $('.del-bill-list').live('click',function(){
        var id = $(this).attr('id').split('-');
        if(id[0]== 'company'){
            $('#company-'+id[1]).remove();
        }
        else if(id[0] == 'restaurant'){
            $('#restaurant-'+id[1]).remove();
        }
        //if no restaurant / company selected remove the interval date field and download button
        if($('.company-bill-lists').html() == null && $('.restaurant-bill-lists').html() == null){
            $('#select-bill-interval-date').fadeOut(800);
            $('#yd-bill-download-button').fadeOut(800);
        }
    });

    //hide wait download picture
    $('#yd-bill-wait-download').hide();

    //hide download button
    $('#yd-bill-download-button').hide();

    //hide select interval date
    $('#select-bill-interval-date').hide();

    //pop up date picker when user click on start date
    if($('#start-bill-interval-date').length > 0){
        initDatepicker('before', 'start-bill-interval-date');
    }

    //pop up date picker when user click on end date
    if($('#end-bill-interval-date').length > 0){
        initDatepicker('before', 'end-bill-interval-date');
    }

    //check if the start date has been filled and end date has been filled, then show download button
    $('#start-bill-interval-date').live('change',function(){
        $('#end-bill-interval-date').live('change',function(){
            $('#yd-bill-download-button').fadeIn(1000);
        });
    });

    //check if the end date has been filled and start date has been filled, then show download button
    $('#end-bill-interval-date').live('change',function(){
        $('#start-bill-interval-date').live('change',function(){
            $('#yd-bill-download-button').fadeIn(1000);
        });
    });

    //add clicked / selected company to list
    $('#all-company').click(function(){
        $('.yd-company-bill-list').append('<label id="company-all" class="company-bill-lists" title="all">ALL&nbsp;<span id="company-all" class="del-bill-list" style="cursor:pointer"><img src="/media/images/yd-icons/cross.png" alt="Löschen" /></span><br /></label>');

        $('#select-bill-interval-date').fadeIn(1000);
    });

    //add clicked /selected restaurant to list
    $('#all-restaurant').click(function(){
        
        $('.yd-restaurant-bill-list').append('<label id="restaurant-all" class="restaurant-bill-lists" title="all">ALL&nbsp;<span id="restaurant-all" class="del-bill-list" style="cursor:pointer"><img src="/media/images/yd-icons/cross.png" alt="Löschen" /></span><br /></label>');
        
        $('#select-bill-interval-date').fadeIn(1000);
    });
    
    //action if download button clicked
    $('#bill-download-button').live('click', function(){
        var company = [];
        var restaurant = [];
        var startdate = $('#start-bill-interval-date').val();
        var enddate = $('#end-bill-interval-date').val();
        var checkstart = $('#start-bill-interval-date').val().split('.');
        var checkend = $('#end-bill-interval-date').val().split('.');
        
        var start = ((checkstart[0]*86400)+(checkstart[1]*2592000)+((checkstart[2]-1970)*31536000));
        var end = ((checkend[0]*86400)+(checkend[1]*2592000)+((checkend[2]-1970)*31536000));
        if(start > end){
            notification('error','Invalid date');
            return false;
        }

        if(startdate == '' || enddate == ''){
            notification('error', 'Invalid date');
            return false;
        }

        $('#yd-bill-wait-download').fadeIn(500);
        $('.company-bill-lists').each(function(){ 
            company.push(this.title);
        });

        $('.restaurant-bill-lists').each(function(){

            restaurant.push(this.title);
        });
        
        $.ajax({
            type: "POST",
            url : '/administration_billing/information',
            data: (
            {
                'companyList'    : company,
                'restaurantList' : restaurant,
                'startdate'      : startdate,
                'enddate'        : enddate
            }
            ),
            success: function(){  
                location.reload();
                $('#yd-bill-wait-download').fadeOut(800);
                $('#yd-bill-download-button').hide();
                $('#select-bill-interval-date').hide();
                $('.company-bill-lists').remove();
                $('.restaurant-bill-lists').remove();
                $('#start-bill-interval-date').val('');
                $('#end-bill-interval-date').val('');
            }
        });
        
        return true;
    });

    $('#add_field').click(function(){
        $('#list').clone().appendTo('#main');
     
    });
    $('#save_ourlink').click(function(){
        $('#load').show();
    });

    $('.yd-trackingcode-details').live('click', function(){
        var id = $(this).attr('id');
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : true
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','details');
                $('#details').load('/request_administration/trackingcodedetails/id/'+id);
            }
        });
        return false;
    });

    // on klick - change the "checked" status of the restaurant
    $('.yd-change-checked-status').live('click', function(){
        var id = $(this).attr('id').split('-')[4];

        $.ajax({
            type: "POST",
            url : '/administration_service/togglechecked',
            data: (
            {
                'restaurantId'    : id
            }
            ),
            success: function(data){
                if (data == 1) {
                    $('#yd-change-checked-status-' + id).css('background-color', '#393');
                    $('#yd-change-checked-status-' + id).html('<div align="center"><b>geprüft</b><br/><a href="#" style="color: #fff">Status auf nicht geprüft setzen</a></div>');
                }
                else if (data == 0) {
                    $('#yd-change-checked-status-' + id).css('background-color', '#f33');
                    $('#yd-change-checked-status-' + id).html('<div align="center"><b>nicht geprüft</b><br/><a href="#" style="color: #fff">Status auf geprüft setzen</a></div>');
                }
                else {
                    alert(data);
                }
            }
        });

        return false;
    });

    // add / remove fidelity points
    $('#yd-add-fidelity-point-count-submit').live('click',function(){
        var custId = $(this).attr('class').split('-')[1];
        var count = parseInt($('#yd-fidelity-point-count').val());
        var comment = $('#yd-manage-fidelity-points-comment').val();

        if ( isNaN(count)) {
            alert('Bitte geben Sie eine Anzahl der Treuepunkte!');
            $('#yd-fidelity-point-count').focus();
            return;
        }

        // trim the string
        if ( comment.replace(/^\s+|\s+$/g, '') == '') {
            alert('Bitte schreiben Sie einen Kommentar!');
            $('#yd-manage-fidelity-points-comment').focus();
            return;
        }        

        $('#yd-fidelity-count').load('/request_administration/addfidelitypoint/custId/' + custId + '/count/' + count + '/comment/' + encodeURIComponent(comment), null, function() {
            $('#fidelity-transactions').load('/request_administration/fidelitytransactions/custId/' + custId);
        });
      
        
        $('#yd-fidelity-point-count').val('1');
        $('#yd-manage-fidelity-points-comment').val('');
    });
    
    $('.yd-remove-fidelity-point').live('click',function(){
               
        var custId = this.id.split('-')[3];
        var transaction = this.id.split('-')[4];
        
        var  confirmed = confirm("Treuepunkte wirklich löschen?"); 
        
        if(confirmed) {         
            $('#yd-fidelity-count').load('/request_administration/removefidelitypoint/custId/' + custId + '/transaction/' + transaction, null, function(){
                $('#fidelity-transactions').load('/request_administration/fidelitytransactions/custId/' + custId);
            });
             
        }
       
        return false;       
    });

    // check the size of textarea
    $('#yd-google-description').live('keyup',function(){
        var text = $(this).val();
        var max = 200;

        if (text.length >= max) {
            $(this).val(text.substring(0, max));
            $('#yd-google-description-status').html('Beschreibung kann nicht länger als ' + max + ' Zeichen sein!');
        }
        else {
            $('#yd-google-description-status').html('Noch ' + (max - text.length) +  ' Zeichen verfügbar');
        }
    });

    /** EDIT ORDERS **/
    $('.yd-editorder-deletemeal').live('click', function(){
        var value = confirm('Wollen Sie diese Speise wirklich löschen?');
        if (!value) {
            return false;
        }
        var bucketId = $(this).attr('id').split('-')[1];
        var orderId = $(this).attr('id').split('-')[3];
        $.ajax({
            type: "POST",
            url: "/request_administration/deletemealfromorder/",
            data: (
            {
                'orderId' : orderId,
                'bucketId' : bucketId
            }
            ),
            success: function(msg){
                notification('success', 'Speise gelöscht');
                // todo: animate it
                location.reload();
            }
        });
        return false;
    });

    $('.yd-editorder-deleteoption').live('click', function(){
        var value = confirm('Wollen Sie diese Option wirklich löschen?');
        if (!value) {
            return false;
        }
        var bucketId = $(this).attr('id').split('-')[3];
        var optionId = $(this).attr('id').split('-')[5];
        $.ajax({
            type: "POST",
            url: "/request_administration/deleteoptionfrommeal/",
            data: (
            {
                'bucketId' : bucketId,
                'optionId' : optionId
            }
            ),
            success: function(msg){
                notification('success', 'Option gelöscht');
                // todo: animate it
                location.reload();
            }
        });
        return false;
    });

    $('.toggleRestaurantStatistics').click(function() {
        var plz = $(this).attr('id').split('-')[2];
        $('#yd-restaurant-plz-statistics-' + plz).toggle();
        return false;
    });

    //activate "save" link in discount code list when the code name was changed
    $('.yd-discount-code-input').live('keyup',function(){
        var id = $(this).attr('id').split('-')[3];
        var name = $('#yd-discount-code-' + id).val();

        $.ajax({
            type: "POST",
            url: "/request_administration/testdiscountcode",
            data:
            {
                'name' : name
            },

            success : function(result){
                if (result == 'ok') {
                    $('#yd-save-discount-code-' + id).html('<b><a href="#' + id + '" class="button yd-discount-code-savelink" id="yd-discount-code-savelink-' + id + '">Speichern</a></b>');
                }
                else {
                    $('#yd-save-discount-code-' + id).html('<span color="#999999">speichern</span>');
                }
            }
        });

    });

    //save discount code name
    $('.yd-discount-code-savelink').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        var name = $('#yd-discount-code-' + id).val();

        if ( name.length == 0 ){
            $('#yd-discount-code-' + id).css('border-color','red');
            return false;
        }
        $('#yd-discount-code-' + id).css('border-color','black');
        
        $.ajax({
            type: "POST",
            url: "/request_administration/savediscountcode",
            data:
            {
                'id' : id,
                'name' : name
            },

            success : function(result){
                if (result != 'ok') {
                    alert(result);
                }
                $('#yd-save-discount-code-' + id).html('<font color="#999999">speichern</font>');
            }
        });
        return true;
    });

    $('.yd-editorder-deleteextra').live('click', function(){
        var value = confirm('Wollen Sie das Exra wirklich löschen?');
        if (!value) {
            return false;
        }
        var bucketId = $(this).attr('id').split('-')[3];
        var extraId = $(this).attr('id').split('-')[5];
        $.ajax({
            type: "POST",
            url: "/request_administration/deleteextrafrommeal/",
            data: (
            {
                'bucketId' : bucketId,
                'extraId' : extraId
            }
            ),
            success: function(msg){
                notification('success', 'Extra gelöscht');
                // todo: animate it
                location.reload();
            }
        });
        return false;
    });

    //in assigning admin rights for restaurants - show link to the user when the user is selected
    $('#yd-user-dropdown').live('change',function(){
        var id = $(this).val();
        var text = $('#yd-user-dropdown :selected').text();

        if (id == -1) {
            $('#yd-selected-user-link').html('');
        }
        else {
            $('#yd-selected-user-link').html('<a href="/administration_user/edit/id/' + id + '" target="_blank">' + text + '</a>');
        }
    });



    /** BEGIN NEWSLETTER SECTION **/
    
    /**
         * show / reload preview of newsletter
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         * @since 04.11.2010
         */
    $('.yd-admin-newsletterpreview-button').live('click', function(){
        var html = $('#yd-admin-newsletterhtml').val();
        $('#yd-admin-newsletterpreview').html(html);
        notification('success','Die Vorschau wurde aktualisiert');
    });

    /**
         * call request to send preview to admin to confirm
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         * @since 04.11.2010
         */
    $('#yd-admin-newsletterpreview-send').live('click', function(){
        var data = $('#yd-admin-newsletterhtml').val();
        $.ajax({
            type: "POST",
            url: "/request_administration/newsletterpreviewsend/",
            data: (
            {
                'data' : data
            }
            ),
            success: function(msg){
                if( msg.split('|')[0] == 1 ){
                    notification('success', 'Eine Vorschauemail mit Bestätigungslink wurde an '+msg.split('|')[1]+' gesendet.');
                }else{
                    notification('error', 'Konnte Email an '+msg.split('|')[1]+' nicht senden', true);
                }
            }
        });
    });


    /**
         * load / reload pattern
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         * @since 04.11.2010
         */
    $('#yd-admin-newsletter-pattern-submit').live('click', function(){
        var path = $('#yd-admin-newsletter-pattern').val();
        if( path <= 0 ){
            notification('warn', 'Bitte Vorlage auswählen!');
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/request_administration/newsletterselectpattern/",
            data: (
            {
                'path' : path
            }
            ),
            success: function(msg){
                $('#yd-admin-newsletterpreview').html(msg);
                $('#yd-admin-newsletterhtml').val(msg);

            }
        });
    });


    /**
         * send newsletter
         * @author Felix Haferkorn <haferkorn@lieferando.de>
         * @since 04.11.2010
         * @param html html to send
         * @param subject string to replace
         */
    function sendNewsletter(html, subject, campaign){

        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'Ok' : true
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','newssend');
                $('.promptfdefaultbutton').hide();
                $.ajax({
                    type: "GET",
                    url: "/request_administration/newslettersend/",
                    data: ({}),
                    success: function(msg){
                        $('#newssend').html(msg);

                        // todo: give chance to abort
                        $.ajax({
                            type: "POST",
                            url: "/request_administration/newslettersend/",
                            data: (
                            {
                                'recipient' : $.map($('#recipients :selected'), function(e) {
                                    return $(e).val();
                                }),//$(this).val(),
                                'html'      : html,
                                'search'    : search,
                                'campaign'  : campaign
                            }
                            ),
                            success: function(msg){
                                $('#yd-newsletter-result').prepend(msg);
                                // show count of sent emails
                                var countToSend = $('#count-recipients').html();
                                var countSent = $('#yd-newsletter-result option').length;
                                var countRest = countToSend-countSent;
                                $('#count-recipients-result').html(countSent+' von '+countToSend+' Emails gesendet (noch '+countRest+')');
                            }
                        });
                    }
                });
            },
            submit: function(v,m,f) {
                if(!v){
                    return true;
                }
            }
        });
        
    }


    $('#yd-newsletter-send').live('click', function(){
        var check = confirm('Wirklich senden?');
        if( !check ){
            return false;
        }
        var html = $('#yd-newsletter-html').text();
        var patternname = $('input[name="patternname"]').val();
        var savepattern = $('input[name="savepattern"]:checked').val();
        var subject = $('input[name="subject"]').val();
        var campaign = $('input[name="campaign"]').val();

        $('#yd-admin-news-send').ajaxForm({
            beforeSubmit: function () {
                $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
                    prefix: 'promptf',
                    buttons: {
                        'OK' : true
                    },
                    loaded: function() {
                        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','result');
                        $('.promptfbuttons').hide();
                    }
                });
            },
            success: function (data) {
                $.ajax({
                    type: "POST",
                    url: "/request_administration/newsletterresult",
                    success: function(msg){
                        $('#result').html(msg);

                        $.each(data.error, function(key,item){
                            $('#yd-newsletter-result-error').append('<option>'+item+'</option>');
                        });

                        $.each(data.success, function(key,item){
                            $('#yd-newsletter-result-success').append('<option>'+item+'</option>');
                        });

                        $('.promptfbuttons').show();
                    }
                });
            },
            dataType: "json"
        });
        
    });

    function countSelectedRecipients(){
        var count = $('#recipients option:selected').length;
        $('#count-recipients').html(count);
    }

    $('#recipients').live('change', function(){
        countSelectedRecipients();
    });

    $('#yd-newsletter-recipients-select-all').live('click', function(){
        $('#recipients option').attr('selected', true);
        countSelectedRecipients();
    });

    $('#yd-newsletter-recipients-deselect-all').live('click', function(){
        $('#recipients option').attr('selected', false);
        countSelectedRecipients();
    });

    $('#yd-newsletter-search-preview').live('click',function(){
        var search = $('#yd-newsletter-search').val();
        var vals = $('#recipients option:selected:first').val().split('|');

        $.ajax({
            type: "POST",
            url: "/request_administration/newsletterpreviewsearchreset/",
            success: function(msg){
                $('#yd-newsletter-html').html(msg);
                $('#yd-newsletter-preview').html(msg);
                $.ajax({
                    type: "POST",
                    url: "/request_administration/newsletterpreviewsearch/",
                    data: (
                    {
                        'search'    : search,
                        'html'      : msg,
                        'replace'   : vals[1]
                    }
                    ),
                    success: function(msg){
                        $('#yd-newsletter-html').html(msg);
                        $('#yd-newsletter-preview').html(msg);
                        notification('success', 'Vorschau geladen');
                    }
                });
            }
        });
        
    });

    $('#yd-newsletter-search-preview-reset').live('click',function(){
        $.ajax({
            type: "POST",
            url: "/request_administration/newsletterpreviewsearchreset/",
            success: function(msg){
                $('#yd-newsletter-html').html(msg);
                $('#yd-newsletter-preview').html(msg);
                $('#yd-newsletter-search').val('');
                notification('success', 'Vorschau zurückgesetzt');
            }
        });
    });

    //change the text of netto/brutto label
    $('#yd-billingasset-brutto-checkbox').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-billingasset-total-kind-text').html('Betrag (Brutto)');
        }
        else {
            $('#yd-billingasset-total-kind-text').html('Betrag (Netto)');
        }
    });

    // add holiday for speciafied federal lands
    $("#yd-add-holiday").live('click',function() {
        var day = $('#yd-opening-holiday').val();
        var name = $('#yd-opening-holiday-name').val();

        if ( day.length == '') {
            alert('Bitte geben Sie einen Tag ein!');
            return;
        }

        $.post(
            "/request_administration/addholiday",
            $("#yd-addholiday-form").serialize(),
            function(){
                $('#yd-opening-holiday').val('');
                $('#yd-opening-holiday-name').val('');
                $('.yd-checkbox').prop('checked', false);
                $('.yd-check-all-checkboxes').prop('checked', false);

                $.post("/request_administration/getholidays", function(response) {
                    $('#yd-holidays-table').html(response);
                });
            });
    });

    // remove holiday
    $(".yd-delete-holiday").live('click',function() {
        var id = this.id.split('-')[3];

        $.post("/request_administration/removeholiday", {
            id : id
        }, function() {
            $.post("/request_administration/getholidays", function(response) {
                $('#yd-holidays-table').html(response);
            });
        });
    });

    // remove holiday by date in all federal lands
    $(".yd-delete-holidaydate").live('click',function() {
        var date = this.id.split('_')[3];

        $.post("/request_administration/removeholidaydate", {
            date : date
        }, function() {
            $.post("/request_administration/getholidays", function(response) {
                $('#yd-holidays-table').html(response);
            });
        });

    });

    // on statistic page: show/hide changes compared to pevious month, in percent
    $('#yd-toggle-show-changes').live('click',function(){
        if ($(this).is(":checked")) {
            $('.yd-stats-change').show();
        }
        else {
            $('.yd-stats-change').hide();
        }
    });

    // list the projectnumbers and department of the company when company was changed
    $('#yd-billingasset-company').live('change',function(){
        var cid = $(this).val();

        var assetId = $('#yd-billingasset-id').val();

        var linebreak = '';

        $('#yd-billingasset-departments').html('');
        $('#yd-billingasset-projectnumbers').html('');

        $.post("/request_billingassets/departments", {
            companyId : cid,
            billingassetId : assetId
        },
        function(departments) {
            if (departments.length > 0) {
                linebreak = '<br/>';
            }
            $('#yd-billingasset-departments').html(departments);
        }
        );

        // some companies have too much project numbers, so the loading time can be extremely long
        $('#yd-admin-backend-wait').show();

        $.post("/request_billingassets/projectnumbers", {
            companyId : cid,
            billingassetId : assetId
        },
        function(projectnumbers) {
            $('#yd-billingasset-projectnumbers').html(linebreak + projectnumbers);
            $('#yd-admin-backend-wait').hide();
        }
        );        
    });

    //activate and deactivate qype id text field
    $('#yd-service-qype-dontlist').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-service-qype-id').val('');
            $('#yd-service-qype-id').prop('disabled', true).prop('readonly', true);
        }
        else{
            $('#yd-service-qype-id').prop('disabled', false).prop('readonly', false);
            
        }
    });

    // show costcenters on company billngs page
    $('.yd-bill-show-costcenters').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        
        $('#yd-bill-costcenters-links-' + id).show();
        $('#yd-bill-costcenters-title-' + id).removeClass('yd-bill-show-costcenters');
        $('#yd-bill-costcenters-title-' + id).addClass('yd-bill-hide-costcenters');
        $('#yd-bill-costcenters-title-' + id).html('Kostenstellen &uarr;');
    });

    // hide costcenters on company billngs page
    $('.yd-bill-hide-costcenters').live('click',function(){
        var id = $(this).attr('id').split('-')[4];

        $('#yd-bill-costcenters-links-' + id).hide();
        $('#yd-bill-costcenters-title-' + id).addClass('yd-bill-show-costcenters');
        $('#yd-bill-costcenters-title-' + id).removeClass('yd-bill-hide-costcenters');
        $('#yd-bill-costcenters-title-' + id).html('Kostenstellen &darr;');
    });

    //activate/deactivate and check/uncheck fields in restaurant payment info, so it stays consistent
    $('#yd-service-onlycash').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-service-paymentbar').prop('disabled', '1');
            $('#yd-service-paymentbar').prop('checked', true);
        }
        else{
            $('#yd-service-paymentbar').prop('disabled', '');
        }
    });

    //activate/deactivate and check/uncheck field in restaurant payment info, so it stays consistent - no cash in premium restaurants
    $('#yd-service-premium').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-service-paymentbar').prop('disabled', true);
            $('#yd-service-paymentbar').prop('checked', false);            
            $('#yd-service-onlycash').prop('disabled', '1');
            $('#yd-service-onlycash').prop('checked', false);            
        }
        else{
            $('#yd-service-paymentbar').prop('disabled', '');
            $('#yd-service-onlycash').prop('disabled', '');
        }
    });
    
    // list the budget groups when company was changed
    $('#yd-company-dropdown').live('change',function(){
        var cid = $(this).val();

        $('#yd-company-budgets').html('');

        $.post("/request_administration/companybudgets", {
            companyId : cid
        },
        function(budgets) {
            $('#yd-company-budgets').html(budgets);
        }
        );
    });

    // send seleted billings
    $('#yd-send-selected').live('click',function(){
        $('.yd-checkbox').each(function(){
            if ( $(this).is(':checked') ){
                var id = $(this).attr('id').split('-')[3];

                if ( $('#yd-viafax-checkbox-' + id).is(':checked') ){
                    var faxNr = $('#yd-viafax-data-' + id).val();

                    if ( faxNr.replace(/^\s+|\s+$/g, '') == '') {
                        alert('Bitte geben Sie eine Faxnummer für die Rechnung ' + id + ' ein!');
                        return false;
                    }
                }

                if ( $('#yd-viamail-checkbox-' + id).is(':checked') ){
                    var email = $('#yd-viamail-data-' + id).val();

                    if ( email.replace(/^\s+|\s+$/g, '') == '') {
                        alert('Bitte geben Sie eine EMail Adresse für die Rechnung ' + id + ' ein!');
                        return false;
                    }
                }
            }
        });

        $('.yd-checkbox').each(function(){
            if ( $(this).is(':checked') ){
                var id = $(this).attr('id').split('-')[3];

                $.post(
                    "/administration_request_billing/send",
                    $("#yd-send-bill-form-" + id).serialize(),
                    function (data) {
                        if (data.success) {
                            notification('success', data.success);
                            $('#yd-bill-status-' + data.id).val(data.status);
                        }

                        if (data.error) {
                            notification('error', data.error);
                        }
                    }, "json"
                    );

            }
        });
    });
   
    // generate fax for the order
    $('.yd-order-generate-fax').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        $.post("/request_administration/generatefaxfororder", {
            id : id
        }, function(data) {
            if (data) {
                if (data.error) {
                    notification('error', data.error);
                }
                else if (data.success){
                    notification('success', data.success);
                }
            }
        }, "json");
    });

    $('.yd-send-bill-button').live('click',function(){
        var id = $(this).attr('id').split('-')[4];

        if ( $('#yd-viafax-checkbox-' + id).is(':checked') ){
            var faxNr = $('#yd-viafax-data-' + id).val();

            if ( faxNr.replace(/^\s+|\s+$/g, '') == '') {
                alert('Bitte geben Sie eine Faxnummer ein!');
                return false;
            }
        }

        if ( $('#yd-viamail-checkbox-' + id).is(':checked') ){
            var email = $('#yd-viamail-data-' + id).val();

            if ( email.replace(/^\s+|\s+$/g, '') == '') {
                alert('Bitte geben Sie eine EMail Adresse ein!');
                return false;
            }
        }
    });

    // change status of rating
    $('a.yd-salesperson-paid').live('click', function(){
        var id = this.id.split('-')[3];
        $.post("/request_administration/togglesalespersonpaid", {
            contractId : id
        }, function (data) {
            var status = 0;
            if (data.state) {
                status = 1;
            }
            $('#yd-salesperson-paid-' + id).html('<img src="/media/images/yd-backend/online_status_' + status + '.png"/>');
        }, "json");
        this.blur();
        return true;
    });

    // show panel with confirm/cancel buttons for exported billing data
    $('#yd-dtaexport-button').live('click', function(){
        $('#yd-confirm-dtaexport-file-panel').show();
        return true;
    });
    
    // set selected billing on payed and hide panel with confirm/cancel buttons for exported billing data
    $('#yd-confirm-dtaexport-button').live('click', function(){
        notification('warn', 'Please wait ... Billing state will be updatet now. Wait until green Message appears');

        var setSuccess = 0;
        var setError= 0;
        
        $('.yd-billing-for-dataus').each(function(){
            if ( $(this).is(':checked') ){
                var billId = this.id.split('-')[4];


                $.ajax({
                    type: "POST",
                    url : '/request_administration/setbillingstatus',
                    data: (
                    {
                        'billingId' : billId,
                        'status'    : 2 
                    }),
                    async: false,
                    success: function(data){
                        if (data.error) {
                            setError = setError + 1;
                        }                        
                        else {
                            setSuccess = setSuccess + 1;
                        }
                    },
                    dataType: "json"
                });
            }
        });
        
        if (setSuccess) {
            notification('success', setSuccess + " bills were set to 'payed'");
        }
        
        if (setError) {
            notification('error', setError + " bills couldn't be set to 'payed'");
        }

        $('#yd-confirm-dtaexport-file-panel').hide();
        return true;
    });

    // hide panel with confirm/cancel buttons for exported billing data
    $('#yd-cancel-dtaexport-button').live('click', function(){
        $('#yd-confirm-dtaexport-file-panel').hide();
        return true;
    });
    
    // show popup with discount code resend formular
    $('.yd-resend-discount-code').live('click',function(){
        var id = $(this).attr('id').split('-')[4];
        $.prompt('<h1>Bitte warten</h1><div id="yd-lb-wait"></div>', {
            prefix: 'promptf',
            buttons: {
                'OK' : true
            },
            loaded: function() {
                $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','discountcode');
                $('#discountcode').load('/request_administration/discountcode/id/'+id);
            }
        });
    });
    
    /**
         *  resend the discount code per sms or per email and renew the lightbox content
         *  @author alex
         *  @since 09.08.2011
         */
    $('.yd-discountcode-resend').live('click', function(){
        var per = this.id.split('-')[3];
        var id = this.id.split('-')[4];
        
        var receiver, gateway;
        if (per == 'tel') {
            receiver = $('#yd-discountcode-resend-tel-value').val();
            gateway = $('#yd-discountcode-resend-tel-gateway').val();
        }
        else {
            receiver = $('#yd-discountcode-resend-email-value').val();            
        }

        $.prompt.getStateContent(jQuery.prompt.getCurrentStateName()).find('.promptfmessage').attr('id','discountcode');
        $('#discountcode').load('/request_administration/discountcode/id/' + id + '/per/' + per + '/receiver/' + receiver + '/gateway/' + gateway);
        $('#promptf_state0_buttonOK').live('click',function(){
            location.reload();
        });
    });    
    
    
    //disable plz url if they shall be created automatically
    $('#yd-city-assemble-urls').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-city-resturl').prop('disabled', true);
            $('#yd-city-caterurl').prop('disabled', true);
            $('#yd-city-greaturl').prop('disabled', true);
        }
        else{
            $('#yd-city-resturl').prop('disabled', false);
            $('#yd-city-caterurl').prop('disabled', false);
            $('#yd-city-greaturl').prop('disabled', false);
        }
    });    
  
    
    // seect text in the field
    $(".yd-discountcheck-readonly-input").live('click',function() {
        $(this).select();
    });

});

function checkcsstemplatename() {
    var templateName = $('#yd-css-template-name').val();
    
    if ( templateName.replace(/^\s+|\s+$/g, '') == '') {
        alert('Bitte geben Sie einen gültigen Namen ein!');
        return false;
    }
        
    var result = true;
    
    $.ajax({
        type: "POST",
        url : '/request_administration/checkcsstemplatename',
        data: (
        {
            'templateName' : templateName
        }),
        async: false,
        success: function(data){
            if (data.state == 1) {
                alert('Unter diesem Namen ist bereits ein Template gespeichert!');
                result = false;
            }
        },
        dataType: "json"
    });    
        
    return result;
}

$('#yd-service-offline-select-all').live('click',function() {
    
    $('input.yd-check-offline[type="checkbox"]').each(function(key, item) {
        if($(item).prop('checked') == true) {
            $(item).prop('checked', false);
        }else{
            $(item).prop('checked', true);
        }
        
    });
   
    
})
