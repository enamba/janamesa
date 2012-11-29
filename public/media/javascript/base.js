/**
 * if we get an 501 we have lost our session, too bad
 * if we get an 500 we need to throw our error page, so no further
 * if the useragent is an Googlebot, we do not fire any requests!
 * harm will be done
 * @author mlaug
 */
$.ajaxSetup({
    beforeSend: function (xhr) {
        if ( navigator.userAgent.indexOf('Googlebot') == -1 ){
            xhr.setRequestHeader("YourdeliveryDoormen", "kjfsdkjhdfsalkhjfdsalkhjfdas");
            return true;
        }
        return false;
    },
    error: function (xhr, status, err) {
        if ( production ){
            // session is gone for order
            if (xhr.status == "501") {
                location.href = '/error/session';
            }

            // we got an error, redirect
            if (xhr.status == "500") {
                location.href = '/error/throw';
            }
        }
    }
});

/**
 * Use unicode with base64
 * This not work :(
 * @author vpriem
 * @since 19.09.2011
 */
//if ($.base64) {
//    $.base64.is_unicode = true;
//}

/**
 * log to console, if firebug exists
 * @author mlaug
 * @since 06.06.2011
 */
function log(string) {  
    if (!production && window.console) {
        console.log(string);
    }
}

/**
 * Fix for jQuery UI Modal Dialog disables scrolling in Chrome
 * see YD-2917
 */
(function($) {
    if ($.ui && $.ui.dialog && $.browser.webkit) {
        $.ui.dialog.overlay.events = $.map(['focus', 'keydown', 'keypress'], function(event) { 
            return event + '.dialog-overlay';
        }).join(' ');
    }
}(jQuery));

/** 
 * @author mlaug
 * @since 05.01.2010
 */
$(document).ready(function(){

    /**
     * Append dialog div to body so that it can be used to apply any dialog
     * @author mlaug
     */
    $('body').append('<div id="dialog" class="yd-dialog-parent hidden"></div>');
    
    $('.yd-money').priceFormat({
        prefix: CURRENCY + " ",
        centsSeparator: ',',
        thousandsSeparator: '.'
    }); 
    
    /**
     * Show a confirmation box on click
     * @author vpriem
     */
    if ($.fn.confirmation) {
        $("a.yd-are-you-sure, :submit.yd-are-you-sure").confirmation();
    }

    /**
     * Submit the select form on change
     * @author vpriem
     */
    $("select.yd-submit-on-change").live('change',function(){
        this.form.submit();
    });

    /**
     * Submit a form on click
     * @author vpriem
     */
    $(".yd-submit-on-click").live("click", function(){
        $(this).closest("form").submit();
        this.blur();
        return false;
    });

    /**
     * Clear an input on click
     * @author mlaug
     */
    $(":input.yd-clear-on-click, :password.yd-clear-on-click").live("click", function(){
        this.value = "";
    });

    /**
     * Lightbox loading
     * @author vpriem, fhaferkorn (16.05.2011)
     */
    $("a.yd-lightbox-loading-on-click").live("click", function(){
        var href = $(this).attr('href');
        $('#dialog').html('<div class="yd-lightbox-loading">&nbsp;</div>');
        location.href = href;
    });

    /**
     * Close lightbox on click
     * @author vpriem
     * @since 14.04.2011
     */
    $(".yd-close-lightbox, .yd-dialogs-close, .be-dialogs-close").live("click", function(){
        closeDialog();
        this.blur();
        return false;
    });

    /**
     * Close and destroys a lightbox on click
     * @author Allen Frank <frank@lieferando.de>
     * @since 06.07.12
     */
    $(".yd-destroy-lightbox, .yd-dialogs-destroy, .be-dialogs-destroy").live("click", function(){
        closeDialog(true);
        this.blur();
        return false;
    });

    $('div.ui-widget-overlay').live('click',function(){
        closeDialog();
    });

    /**
     * Allow only numbers to be typed in
     * allow - for poland
     * @author vpriem
     */
    $(':input.yd-only-nr').live('keyup', function(event){
        if (event.which != 13) { // enter key
            this.value = this.value.replace(/[^\d\-]/g, "");
        }
    });

    /**
     * Allow only alphanumeric characters
     * @author Allen Frank <frank@lieferando.de>
     */
    $(':input.yd-only-alphanumeric').live('keyup', function(event){
        this.value = this.value.replace(/[\s]/g, "");
    });

    /**
     * Empty text, display a default text
     * and remove it by typing
     * @author vpriem
     * @since 09.02.2011
     */
    $(':input.yd-empty-text, :password.yd-empty-text, textarea.yd-empty-text').emptytext();
    
    /**
     * Open a popup
     * @author vpriem
     * @since 09.02.2011
     */
    $("a.yd-popup").live('click', function(){
        popup(this.href, 'yd_popup', 650, 550);
        this.blur();
        return false;
    });

    /**
     * Form validation
     * @author vpriem
     * @since 15.02.2011
     */
    if ($.validationEngine) {
        if ($("form.yd-validation").length) {
            $("form.yd-validation").validationEngine({
                promptPosition: "topRight",
                validationEventTriggers: "blur",
                success: false,
                scroll: false
            });
        }
    }

    /**
     * Tooltips
     * @author vpriem
     * @since 16.02.2011
     */
    if ($.fn.tooltip) {
        $('.tooltip').tooltip();
    }

    /**
     * Autogrow
     * @author vpriem
     * @since 16.02.2011
     */
    if ($.fn.autogrow) {
        $("textarea").autogrow();
    }
    
    /**
     * @author mlaug
     * @since 28.04.2011
     */
    $('.copy-on-click').live('click', function(){
        $.copy($(this).html()); 
    });

    /**
     * Toggle helper
     * @author vpriem
     * @since 10.06.2011
     */
    $("a.yd-toggle, a.yd-fade-toggle, a.yd-slide-toggle").each(function(){
        if (this.hash === undefined) {
            return;
        }
        
        if (location.hash.length && location.hash == this.hash) {
            $(this.hash).show();
        }

        var labels = $(this).html().split("|");
        if (labels.length < 2) {
            labels.push(labels[0]);
        }

        if ($(this.hash + ":hidden").length) {
            labels = labels.reverse();
        }

        $(this)
        .html(labels[0])
        .toggle(function(){
            $(this).html(labels[1]);
        }, function(){
            $(this).html(labels[0]);
        })
        .click(function(){
            $(this.hash).toggle();
            this.blur();
            return false;
        });
    });

    //main tab is everywhere so put this in base.js
    /**
     * @todo: remove once great and fruit have been merged
     */
    $('#yd-tab-3, #yd-nav-layer').live('mouseover',function(){
        $('#yd-nav-layer').show();
    });
    $('#yd-tab-3, #yd-nav-layer').live('mouseout',function(){
        $('#yd-nav-layer').hide();
    });
    
    /**
     * @author vpriem
     * @since 04.07.2011
     */
    if ($.fn.formToggle) {
        $("form.yd-form-toggle").formToggle();
        $("a.yd-form-toggle").live('click',function(event){
            event.preventDefault();
            $(this)
            .blur()
            .closest("form")
            .formToggle();
        });
    }
    
});
