
/**
 *javascript for infocenter
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 02.05.2011
 */
$(document).ready(function(){

    /**
     * validate & submit contact form "firma anmelden"
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     */
    $('#yd-form-contact-company-submit').live('click', function(){

        $('.yd-form-contact-form').css('border', '1px solid #555555');

        var name = $('input[name=name]');
        var email = $('input[name=email]');
        var tel = $('input[name=tel]');
        var comp = $('input[name=comp]');
        var ort = $('input[name=ort]');
        var msg = $('textarea[name=msg]');

        // check required values not empty
        var correct = true;
        if(name.val().length < 5){
            name.css('border', '1px solid red');
            correct = false;
        }
        if(email.val().length < 5){
            email.css('border', '1px solid red');
            correct = false;
        }
        if(comp.val().length < 5){
            comp.css('border', '1px solid red');
            correct = false;
        }
        if(msg.val().length < 5){
            msg.css('border', '1px solid red');
            correct = false;
        }

        if(correct != true){
            notification('error', 'Bitte prüfen Sie Ihre Eingabe');
            return false;
        }else{

            $.ajax({
                type : "POST",
                dataType : 'json',
                url: "/request_info/contactcompany",
                data: ({
                    'email' : email.val(),
                    'name'  : name.val(),
                    'ort'   : ort.val(),
                    'comp'  : comp.val(),
                    'tel'   : tel.val(),
                    'msg'   : msg.val()
                }),
                success : function(json){

                    if ( json.result === false ){
                        notification('warn',json.msg);
                        return false;
                    }

                    notification('success',json.msg);
                    $('form#yd-form-contact-company').hide();
                    $('div#yd-form-contact-company-thx').show();
                    $('span.yd-infoform-step1').html('1. Anfrage');
                    $('span.yd-infoform-step2').html('<strong>2. Kontakt</strong>');
                },
                error : function(){
                    notification('error','Nachricht konnte nicht gesendet werden');
                }
            });
        }
    });


    /**
     * open link for "Firma anmelden"
     * @author Felix Haferkorn
     * @since 28.03.2012
     */
    $('#yd-info-register-company').live('click', function(){
        location.href = "/info/firmaanmelden";
        return false;
    });

    /**
     * "Kostenrechner"
     * @author Felix Haferkorn
     * @since 28.03.2012
     */
    if($("#slider").length > 0){
        $("#slider").slider({
            value:15,
            min: 5,
            max: 50,
            step: 5,
            slide: function(event, ui) {

                var countBelege = $('#count-belege').val();
                var valueSlider = ui.value;
                var costYd = $('#cost-with-yd').html().split(' ')[0];

                if(countBelege != 0 && valueSlider != 0){
                    $('#cost-before').html((countBelege*valueSlider) + ' €');
                    $('#win-with-yd').html(((countBelege*valueSlider) - costYd) +' €');
                    $('#slider-val').val(ui.value);
                }
            }
        });
        $('#slider-val').val('10');
        $('#cost-before').html($('#count-belege').val()*15 + ' €');
        $('#win-with-yd').html((($('#count-belege').val()*15) - $('#cost-with-yd').html().split(' ')[0]) +' €');

        $('#count-belege').keyup(function(){
            var countBelege = $('#count-belege').val();
            var valueSlider = $('#slider-val').val();
            var costYd = $('#cost-with-yd').html().split(' ')[0];

            if(countBelege != 0 && valueSlider != 0){
                $('#cost-before').html((countBelege*valueSlider) + ' €');
                $('#win-with-yd').html(((countBelege*valueSlider) - costYd) +' €');
                $('#slider-val').val(valueSlider);
            }
        });
    }

    /**
     * show, hide and select dropdown for proposal category
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     */
    $('#sort').live('click', function(){
        $('#yd-sorting-middle-list').toggle();
    });

    $('.yd-sorting-middle-list').live('click', function(){
        var val = $(this).html();
        $('#yd-sorting-default').hide();
        $('#yd-sorting-value').html(val).show();
        $('input[name=dropdownval]').val(val);
        $('#yd-sorting-middle-list').toggle();
    });

    /**
     * Restaurant vorschlagen Besitzer Checkbox
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.08.2012
     */
    $('#yd-infocenter-propose-owner').click(function(){
        if($(this).prop('checked') == 1) {
             $('input[name="ownerName"]').show();
        }else {
            $('input[name="ownerName"]').hide();
        }
    });

    /**
     * Restaurant vorschlagen
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.05.2011
     */
    $('#yd-proposal-submit').live('click', function(){
        $('.yd-form-input').css('border', '1px solid #CCCCCC');

        var correct = true;
        $('.yd-form-input').each(function(){
            if ($(this).hasClass('yd-empty-text') && !$(this).hasClass('yd-not-required')) {
                correct = false;
                $(this).css('border', '1px solid red');
            }
        });

        var service = $('input[name="service"]');
        var street = $('input[name="street"]');
        var ort = $('input[name="ort"]');
        var tel = $('input[name="tel"]');
        var category = $('input[name="dropdownval"]');
        var isOwner = $('input[name="isOwner"]');
        var name = $('input[name="ownerName"]');

        if (isOwner.is(':checked') && name.val().length <= 3) {
            name.css('border', '1px solid red');
            correct = false;
        }

        if (service.val().length < 3) {
            service.css('border', '1px solid red');
            correct = false;
        }

        if (street.val().length < 5) {
            street.css('border', '1px solid red');
            correct = false;
        }

        if (ort.val().length < 5) {
            ort.css('border', '1px solid red');
            correct = false;
        }

        if (tel.length && tel.val().length < 5) {
            tel.css('border', '1px solid red');
            correct = false;
        }

        if (!correct) {
            notification('error', $.lang("check-red-fields"));
            return false;
        }

        $('#yd-proposal-submit').hide();

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "/request_info/proposal",
            data: {
                'service': service.val(),
                'street': street.val(),
                'ort': ort.val(),
                'category': category.val(),
                'telefon':  tel.length ? tel.val() : "",
                'name': name.length ? name.val() : ""
            },
            success: function(json){
                if (json.result === false) {
                    notification('warn', json.msg);
                    $('#yd-proposal-submit').show();
                    return false;
                }

                $('#yd-restproposal').hide();
                $('#yd-restproposal-thx').show();

            },
            error: function(){
                notification('error', 'Could not send message');
                $('#yd-proposal-submit').show();
                return false;
            }

        });
    });

    $('#yd-register-restaurant-submit').live('click', function(){
        $('.yd-form-input').css('border', '1px solid #CCCCCC');

        var service = $('input[name=service]');
        var name = $('input[name=name]');
        var street = $('input[name=street]');
        var city = $('input[name=city]');
        var telefon = $('input[name=telefon]');
        var mobil = $('input[name=mobil]');
        var email = $('input[name=email]');
        var contacttime = $('input[name=dropdownval]');

        var correct = true;
        if(service.val().length < 3 || !service.hasClass('yd-form-valid')){
            service.css('border', '1px solid red');
            correct = false;
        }

        if(name.val().length < 3 || !name.hasClass('yd-form-valid')){
            name.css('border', '1px solid red');
            correct = false;
        }

        if(street.val().length < 5 || !street.hasClass('yd-form-valid')){
            street.css('border', '1px solid red');
            correct = false;
        }

        if(city.val().length < 5 || !city.hasClass('yd-form-valid')){
            city.css('border', '1px solid red');
            correct = false;
        }

        if(correct != true){
            return false;
        }

        $('#yd-register-restaurant-submit').hide();

        $.ajax({
            type : "POST",
            dataType : 'json',
            url: "/request_info/registerrestaurant",
            data: ({
                'service': service.val(),
                'name'   : name.val(),
                'street' : street.val(),
                'ort'   : city.val(),
                'telefon': telefon.val(),
                'mobil'  : mobil.val(),
                'email'  : email.val(),
                'contacttime': contacttime.val()
            }),
            success : function(json){
                if ( json.result === false ){
                    notification('warn',json.msg);
                    $('#yd-register-restaurant-submit').show();
                    return false;
                }

                $('#yd-registerrestaurant').hide();
                $('#yd-registerrestaurant-thx').show();

            },
            error : function(){
                notification('error','Could not send message');
                $('#yd-register-restaurant-submit').show();
                return false;
            }

        });
    });


    /**
     * validate and submit contact form (FAQ & Kundenservice) in INfoCenter
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.05.2011
     */
    $('#infocenter-contact-submit').live('click', function(){
        var submit = this;

        $('.yd-infocenter-form-input').css('border', '1px solid #555555');

        var name = $('input[name="name"]');
        var email = $('input[name="email"]');
        var tel = $('input[name="tel"]');
        var comp = $('input[name="comp"]');
        var ort = $('input[name="ort"]');
        var msg = $('textarea[name="message"]');

        var correct = true;

        name.css('border', '');
        if (name.val().length < 3) {
            name.css('border', '1px solid red');
            correct = false;
        }

        email.css('border', '');
        if (email.val().length < 5) {
            email.css('border', '1px solid red');
            correct = false;
        }

        msg.css('border', '');
        if (msg.val().length < 5) {
            msg.css('border', '1px solid red');
            correct = false;
        }

        if (!correct){
            notification('error', $.lang('contact-check-red'));
            return false;
        }

        $(submit).hide();
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "/request_info/contactform",
            data: {
                'name': name.val(),
                'email': email.val(),
                'tel': tel.val(),
                'comp': comp.val(),
                'ort': ort.val(),
                'message': msg.val()
            },
            success: function(json){
                $(submit).show();

                if (json.result === false) {
                    notification('error', json.msg);
                    return;
                }

                notification('success', json.msg);
                $('.yd-infocenter-form-input').val('');

            },
            error: function(){
                $(submit).show();

                notification('error', $.lang('contact-could-not-send'));
            }
        });

    });

});