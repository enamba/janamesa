var YdMode = "rest";

$(document).ready(function(){

    /**
     * login toggle
     * @author oknoblich
     * @since 06.03.2012
     */

    $(".yd-header-login").live('click', function(){
        $(".yd-login-window").toggle();
        $(this).toggleClass("active");
    });

    $(".yd-login-window-toggle").live('click', function(){
        $('.yd-login-window-top, .yd-login-window-bottom').slideToggle();
        $(this).toggleClass("active");
    });
    
    /**
     * close login window on click outside
     * @author bseifert
     * @since 30.08.2012
     */
    
    if($('.yd-login-window').length > 0){
        $('body').bind('click', function(event){
            var $target = $(event.target);
            if($target.parents('.yd-login-window').length > 0 || $target.hasClass('yd-header-login')){
                return;
            }        
            $('.yd-login-window').hide();
            $('.yd-header-login').removeClass('active');
        });
    }

    /**
     * button shining
     * @author oknoblich
     * @since 15.02.2012
     */

    var shine = function(){
        setTimeout(function(){
            $(".shine").animate({
                backgroundPosition: '350px'
            },1000, function(){
                $(".shine").css("background-position","-100px");
                shine();
            }); 
        }, 2000);
    };
    shine();

    /**
     * tabs global
     * @author oknoblich
     * @since 07.03.2011
     */

    $('#tabs').tabs({
        fx: {
            opacity: 'toggle'
        }
    });
    $('#subtabs').tabs({
        fx: {
            opacity: 'toggle'
        }
    });
    $('#subtabs2').tabs({
        fx: {
            opacity: 'toggle'
        }
    });

    /**
     * scroll to top button
     * @author oknoblich
     * @since 08.12.2011
     */

    $().UItoTop({
        easingType: 'easeOutQuart'
    });

    /**
     * button loading animation on click
     * @author oknoblich
     * @since 29.11.2011
     */

    $('.yd-button-140, .yd-button-190, .yd-button-240, .yd-button-250, .yd-button-280').wrapInner('<span></span>');

    /**
     * simple tooltip
     * @author oknoblich
     * @since 29.11.2011
     */

    $("#yd-clear-bucket, .yd-menu-modern strong img, .td-edit, .td-check, .td-heart, .td-letsgo, .td-delete, .td-thumb, .td-show, .td-star, .td-repeat").simpletooltip();

    /**
     * how it works
     * @author oknoblich
     * @since 27.07.2012
     */

    $("#yd-howitworks-new, .yd-header-how").live('click', function(){
        openDialog('/request/howitworks', {
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        });
        return false;
    });

    /**
     * jQuery UI CSS only for Datepicker
     * @author oknoblich
     * @since 08.09.2011
     */

    $(document).ready(function() {
        $('#ui-datepicker-div').wrap('<div class="yd-jquerycss-prison"></div>');
    });

    /**
     * Dynamic dialog creation
     * @author mlaug
     * @since 24.06.2011
     */

    if ($('.yd-open-dialog-on-load').length) {
        var popup = $('.yd-open-dialog-on-load');
        popup.dialog({
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            },
            beforeClose: function(){
                return false;
            }
        });
        $('div.ui-dialog').attr('id','dialog-' + popup.attr('id'));       
    }

    /**
     * Cookie detection
     * @author vpriem
     * @since 10.02.2011
     */
    if (!isCookieEnabled()) {
        notification('error', "Sie können diese Seite leider nicht ohne Cookies verwenden!", true);
    }

    /**
     * Form validation and infos
     * @author vpriem
     * @since 10.02.2011
     */
    $(':input.yd-form-invalid').each(function(){
        if (this.value.length) {
            $(this)
            .removeClass('yd-form-invalid')
            .addClass('yd-form-valid');
        }
    });
    $(':input.yd-form-input')
    .live('focus', function(){
        $(".yd-form-info, .yd-form-info-left").hide();
        $(this)
        .closest("ul")
        .find(".yd-form-info")
        .show();
    })
    .live('blur', function(){
        if (this.value.length) {
            $(this)
            .removeClass('yd-form-invalid')
            .addClass('yd-form-valid');
        }
        else{
            $(this)
            .removeClass('yd-form-valid')
            .addClass('yd-form-invalid');
        }
        $(this)
        .closest("ul")
        .find(".yd-form-info")
        .hide();
    });

    /**
     * Check plz
     * @author vpriem
     * @since 10.02.2011
     */
    $('#yd-form-register-plz').live('blur', function(){
        var $input = $(this);
        $.ajax({
            url: '/request/ort/',
            data: {
                plz: this.value
            },
            success: function (json) {
                $('#dynamic-ort').html(json.ort);
            },
            error: function(){
                $('#dynamic-ort').html('Ort nicht gefunden');
                $input
                .removeClass('yd-form-valid')
                .addClass('yd-form-invalid');
            },
            dataType: "json"
        });
    });
 
    /**
     * Show user informations
     * @author vpriem
     * @since 25.07.2011
     */
    if (ydState.maybeLoggedIn()) {
        $('.yd-logged-out').hide();
        $('.yd-logged-in').show();
        
        $('.yd-customer-name')
        .html(ydCustomer.getFullname());
        if (ydCustomer.getFullname().length > 25) {
            $('.yd-customer-name')
            .html(ydCustomer.getFullname().slice(0, 25) + "...")
            .attr("title", ydCustomer.getFullname())
            .tooltip();
        }
        
        if (ydCustomer.getCompany()) {          
            $('.yd-customer-company')
            .html(' | ' + ydCustomer.getCompany())
            .show();
            if (ydCustomer.getCompany().length > 25) {
                $('.yd-customer-company')
                .html(' | ' + ydCustomer.getCompany().slice(0, 25) + "...")
                .attr("title", ydCustomer.getCompany())
                .tooltip();
            }
        }
        
        if (ydCustomer.isAdmin()) {
            $("#yd-customer-company-admin").show();
        }
        if($('#fb-connect').length) {
            window.fbAsyncInit = function() {
                $('.yd-form-facebook-block').show();
                fbAsyncInitConnect();
                $('.yd-facebook-login').live('click',function(){
                    FB.login(function(response) {
                        if (response.authResponse) {
                            location.href = "/user/fb-connect/?redirect_url=" + encodeURI(document.location.href);
                        }
                    }, {
                        scope: 'email,user_birthday,publish_actions'
                    });
                });
            }
        }
    } else if(typeof(FbAppId) != "undefined") {
        window.fbAsyncInit = function() {
            fbAsyncInitConnect();
            FB.getLoginStatus(function(response) {
                $('.yd-login-window-fb, #fb-login, .yd-form-facebook-block').show();
                if (response.status === 'connected') {
                    $('.yd-facebook-login').prop("href", "/user/fb-login/?redirect_url=" + encodeURI(document.location.href));
                } else {
                    $('.yd-facebook-login').live('click',function(){
                        FB.login(function(response) {
                            if (response.authResponse) {
                                location.href = "/user/fb-login/?redirect_url=" + encodeURI(document.location.href);
                            }
                        }, {
                            scope: 'email,user_birthday,publish_actions'
                        });
                    });
                }
            });
        }
    }
    
    /**
     * Go back
     * @author vpriem
     * @since 29.09.2011
     */
    $(".yd-go-back").live('click', function(){
        historyBack();
        return false;
    });
    
});
