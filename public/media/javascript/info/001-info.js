/**
 * Need to be reviewed
 */
$(document).ready(function(){

    if( $('#yd-info-email-goto').val() != '' ){
        var destination = $('#yd-info-email-goto').val();
        switch(destination){
            case 'hilfe':
                window.open('/faq', 'Hilfe', ',width=440,height=440,left=50,top=50,scrollbars=1');
                break;
            case 'vorschlagen':
                location.href = '/info/';
                break;
            case 'impressum':
                window.open('/impressum', 'Impressum', ',width=440,height=440,left=50,top=50,scrollbars=1');
                break;
        }
    }

    /* sub navigation */
    $('.yd-info-subnav').live('click', function(){
        var val = $(this).attr('id');
        var destination = str_replace('-subnav', '', val);
        // subnav
        $('.yd-info-subnav').removeClass('active');
        $(this).addClass('active');
        // content tabs
        $('.info-content').addClass('hidden');
        $('#'+destination).removeClass('hidden');
        return false;
    });

    
    $('#yd-info-register-company').live('click', function(){
        location.href = "/info/firmaanmelden";
        return false;
    });

    $('#yd-info-download-prospekt').live('click', function(){
        location.href = "/storage/unternehmensbroschuere.pdf";
        return false;
    });
    
    $('#yd-info-download-logo-eps').live('click', function(){
        location.href = "/storage/logo-1.eps";
        return false;
    });
    
     $('#yd-info-download-logo-png').live('click', function(){
        location.href = "/storage/logo-1.png";
        return false;
    });



    $('#yd-info-download-pr-1').live('click', function(){
        location.href = "/storage/pr/10_06_07_yd_pm_kostenfalle_mitarbeiterverpflegung.pdf";
        return false;
    });



    /* contact form sidebar */
    if( $('#yd-info-sidebar-contact-form').length > 0 ){
        $('#yd-info-sidebar-contact-form').validationEngine({
           promptPosition: "topRight",
           validationEventTriggers:"blur",
           success :  false,
           scroll: true
           }
        );
        $('#yd-info-sidebar-contact-form-submit').live('click', function(){
            if($('#yd-info-sidebar-contact-form').validationEngine({returnIsValid: true}) == true){
                var name = $('#yd-info-sidebar-contact-form-name').val();
                var email = $('#yd-info-sidebar-contact-form-email').val();
                var tel = $('#yd-info-sidebar-contact-form-tel').val();
                var comp = $('#yd-info-sidebar-contact-form-comp').val();
                var ort = $('#yd-info-sidebar-contact-form-ort').val();
                var message = $('#yd-info-sidebar-contact-form-message').val();
                $.ajax({
                    type: "POST",
                    url:'/request_info/sendcontactform/',
                    data: ({
                        'name'  : name,
                        'email' : email,
                        'tel'   : tel,
                        'comp'  : comp,
                        'ort'   : ort,
                        'message': message
                    }),
                    success : function(xmldata){
                        $('#yd-info-sidebar-form-content').fadeOut('fast');
                        $('#yd-info-sidebar-form-thx').fadeIn('fast');
                    }
                });
                
            }
            return false;
        });
    }

    /* contact form main content */
    if( $('#yd-info-main-contact-form').length > 0 ){
        $('#yd-info-main-contact-form').validationEngine({
           promptPosition: "topRight",
           validationEventTriggers:"blur",
           success :  false,
           scroll: true
           }
        );
        $('#yd-info-main-contact-form-submit').live('click', function(){
            if($('#yd-info-main-contact-form').validationEngine({returnIsValid: true}) == true){
                var name = $('#yd-info-main-contact-form-name').val();
                var email = $('#yd-info-main-contact-form-email').val();
                var tel = $('#yd-info-main-contact-form-tel').val();
                var comp = $('#yd-info-main-contact-form-comp').val();
                var ort = $('#yd-info-main-contact-form-ort').val();
                var message = $('#yd-info-main-contact-form-message').val();
                
                $.ajax({
                    type: "POST",
                    url:'/request_info/sendcontactform/',
                    data: ({
                        'name'  : name,
                        'email' : email,
                        'tel'   : tel,
                        'comp'  : comp,
                        'ort'   : ort,
                        'message': message
                    })
                });
                $('#yd-info-main-form-content').fadeOut('fast');
                $('#yd-info-main-form-thx').fadeIn('fast');
            }
            return false;
        });
    }



    /* Kostenrechner - Slider */
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

});
