/**
 * Class for handling of Discount ajax and Code Verification
 * each step increments currentStep by one
 *  @author Daniel Hahn <hahn@lieferando.de>
 *  @since 20.01.2012
 */
var DiscountVerification = function () {
    /* Init Part */
    this.currentStep = 1;
    
    this.classBase = "yd-discount-progress-";
    
    this.base = null;
    
    this.referer = null;
    
    this.sendOK = null;        
    this.timeoutRunning = false;
    
    /**
     * Substitute reference for this
     * $this should refer to a jQuery Object, thats the convention
     * DiscountVerification should be used instead
     */
    var $this = this;
    
    
    /**
     * startup function only when class yd-discount-2012 exist
     */
    this.init = function() {
       
        this.base = $('.yd-discount-2012');
        
        if (this.base.length) {
            log(this.base);
            
            var baseId = this.base[0].id.split('-');
            baseId.shift();
            this.currentStep = baseId.shift();
            this.referer = baseId.join('-'); // cause referer may contains some "-"
            
            $('.yd-discount-submit')
            .on('click', $this.submit)
            .closest("form").submit(function(){
                /**
                     * Thats tricky we should use ajaxSubmit
                     * with input submit instead
                     */
                return false;
            });
            $('.yd-discount-reset').on('click', $this.reset);
            
        

            //add validation to tel number
            $('.send-sms').on('keyup',function(){
                if ( this.value.length > 0 && this.value.charAt(0) == 0 ){
                    $this.showErrors({
                        status : 'NOK',
                        response : $.lang('telephone-not-valid')
                    }, $(this).closest('form'));
                }
                else{
                    $('.formError').hide();
                }
                this.value = this.value.replace(/[^\d]/g, ""); //replace non numeric values
                this.value = this.value.replace(/^0/, "");
            });
        
            this.updateView();
        
        }
    };
    
    /**
     *
     * Generic submit for forms, with call of actions
     */
    this.submit = function() {
                
        log('DiscountVerification.submit');            
        var form = $(this).closest('form');
        $('#yd-discount-evaluation-wait').show();
            
        if(form) {
            $(form).ajaxForm({
                beforeSubmit: function(arr, $form, options) {
                    arr.push({
                        name: "referer", 
                        value: $this.referer
                    }); 
                },
                
                success:   function(response){
                    $('#yd-discount-evaluation-wait').hide();
                    var data = $.parseJSON(response);
                    log($this);
                    switch(this.url) {
                        case "/request_discount/code" :
                            $this.handleCode(data,form);
                            break;
                        
                        case "/request_discount/email" :
                            $this.handleEmail(data,form);
                            break;
                        
                        case "/request_discount/tel" :
                            $this.handleTel(data,form);
                            break;
                        
                        case "/request_discount/telcode" :
                            $this.handleTelcode(data,form);
                            break;
                        
                    }
                },
                
                error : function(){
                    $this.handleTelcode({
                        status : 'NOK',
                        response : 'Server Error'
                    },form);
                }
            });
           
            $(form).submit();
        }
        log(form);
    };
    
    /**
     * Catch all clicks on reset and resend, without forms, based on ids
     */
    this.reset = function() {
        var id = this.id;    
        log(this);
        
        switch(id) {
            case "send-mail":
                $this.handleSendEmail(this);
                break;
                
            case "send-sms":
                $this.handleSendSms(this);
                break;
            
            case "send-code":
                $this.handleSendCode(this);
                break;
        }
        
    };
    
    /* Handler Part */
    
    /**
     * handle code verification
     */
    this.handleCode = function(data, form) {
        log('DiscountVerification.handleCode');
        log(data);
        if(data.status === "OK") {
            this.currentStep = 2;
            this.updateView();
        }else if(data.status === "NOK") {
           
            this.showErrors(data, form);
        }
        
    };
    
    
    /**
     * handle Email Verification
     */
    this.handleEmail = function(data, form) {
        log('DiscountVerification.handleEmail');
        log(data);
        log(form);     
        
        if(data.status === "OK") {
            this.currentStep = 3;
            this.updateView();
        }else if(data.status === "NOK") {
           
            this.showErrors(data, form);
        }
    };
    
    
    /**
     * handle telephone Number Verification
     */
    this.handleTel = function(data, form) {
        log('DiscountVerification.handleTel');
        log(data);
        if(data.status === "OK") {
            this.currentStep = 5;
            this.updateView();
        }else if(data.status === "NOK") {
           
            this.showErrors(data, form);
        }
    };
    
    /**
     * handle telephone code Verification and show final lightbox
     */
    this.handleTelcode = function(data,form) {
        log('DiscountVerification.handleTelcode');
        log(data);
        if(data.status === "OK") {
            //            this.currentStep = 6;
            //            this.updateView();           
            var options = {
                width : 600,
                height: 380,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                },
                beforeClose: function() {
                    window.location.href="/";
                }
            };
            
            $('#dialog')
            .html(data.response)
            .dialog(options);
            $('.formError').hide();
            $('.yd-discount-reset').off('click', $this.reset);
            $('.yd-discount-reset').on('click', $this.reset);

        }else if(data.status === "NOK") {
           
            this.showErrors(data, form);
        }
    };
    
    /* reset and resend part */
    
    this.handleChangeData = function() {
        log('DiscountVerification.handleChangeData');
        this.currentStep = 2;
        $(this.base).removeClass(this.classBase + 3);
        $(this.base).addClass(this.classBase+this.currentStep);
    };
    
    this.handleSendEmail = function(elem) {
        log('DiscountVerification.handleSendEmail');
        var referer = $this.referer;
        log(referer);
        $('#yd-discount-evaluation-wait').show();
        
        $.ajax({
            type: "post",
            url: "/request_discount/resendmail",
            data: "referer="+referer,
            success: function(response){
                $('#yd-discount-evaluation-wait').hide();
                
                var data = $.parseJSON(response);
                
                if(data.status === "NOK") {
                    $('#yd-discount-email-verification-data').html(data.response);
                    $('#yd-discount-email-verification-data').removeClass('question');
                }
                else {
                    log('DiscountVerification.sentEmail');                    
                }
            }
        });                    
    };
    
    this.handleSendSms = function(elem) {
        log('DiscountVerification.handleSendSms');
        
        var referer = $this.referer;
        log(this.timeoutRunning);
        if(this.sendOk != null) {
            $('#yd-discount-evaluation-wait').show();
            
            $.ajax({
                type: "post",
                url: "/request_discount/resendsms",
                data: "referer="+referer,
                success: function(response){                    
                    log(response);
                    var data = $.parseJSON(response);
                    
                    $('#yd-discount-evaluation-wait').hide();
                                        
                    if(data.status === "NOK") {
                        $('#yd-discount-tel-verification-data').html(data.response);
                    }
                    else {
                        log('DiscountVerification.sentSms');
                        $this.sendOk = null;
                        $(elem).next().show();
                        $('#timeout-sms').text("120");
                    }                    
                }
            });      
        }else if(!this.timeoutRunning) {            
            this.timeoutRunning = true;
            
            var counter = $('#timeout-sms');
            var time = counter.text();                
            var elemColor = $(elem).css('color');
            var elemCursor = $(elem).css('cursor');
            var elemUnderline = $(elem).css('text-decoration');
            
            $(elem).css('color', '#777777');
            $(elem).css('cursor', 'text');
            $(elem).css('text-decoration', 'none');
            
            var interval = window.setInterval(function() {                                            
                time--;
                counter.text(time);
                if(time < 1) {
                    clearInterval(interval);
                    $this.sendOk = 1;
                    $(elem).next().hide();
                    $this.timeoutRunning = false;
                    $(elem).css('color', elemColor);
                    $(elem).css('text-decoration', elemUnderline);
                    $(elem).css('cursor', elemCursor);
                }                                 
            }, 1000);
        }
    };
    
    this.handleSendCode = function(elem) {
        log('DiscountVerification.handleSendCode');
         
        var referer = $this.referer;
        log(referer);
        if(this.sendOk != null) {
            $.ajax({
                type: "post",
                url: "/request_discount/resendcode",
                data: "referer="+referer,
                success: function(){               
                    log('DiscountVerification.sentCode');         
                    $this.sendOk = null;
                    $(elem).next().show();
                    $('#timeout-code').text("120");
                }
            });    
        }else if(!this.timeoutRunning) {            
            this.timeoutRunning = true;
            
            var counter = $('#timeout-code');
            var time = counter.text();                
            var elemColor = $(elem).css('color');
            var elemCursor = $(elem).css('cursor');
            var elemUnderline = $(elem).css('text-decoration');
            
            $(elem).css('color', '#777777');
            $(elem).css('cursor', 'text');
            $(elem).css('text-decoration', 'none');
            
            var interval = window.setInterval(function() {                                            
                time--;
                counter.text(time);
                if(time < 1) {
                    clearInterval(interval);
                    $this.sendOk = 1;
                    $(elem).next().hide();
                    $this.timeoutRunning = false;
                    $(elem).css('color', elemColor);
                    $(elem).css('text-decoration', elemUnderline);
                    $(elem).css('cursor', elemCursor);
                }                                 
            }, 1000);
        }   
    };
            
    /* view part */
    
    this.updateView = function(){
        log('DiscountVerification.updateView to step ' + this.currentStep);
        $('.formError').hide();
        log(this.classBase+this.currentStep);
        $(this.base).removeClass(this.classBase + (this.currentStep-1));
        $(this.base).addClass(this.classBase+this.currentStep);
        //disable everything which is not needed
        //loop to 10, if any step will be added :)
        for(var i=0;i<6;i++){
            if ( i != this.currentStep && i <= 3 && this.currentStep <= 3){
                log('disable input of step ' + i);
                $('.yd-step'+i+' input').prop('disabled',true);
            }
            else{
                $('.yd-step'+i+' input').prop('disabled',false);
            }
        }
        
    };
    
    this.showErrors = function(data, form) {
        log('DiscountVerification.showErrors');
        $('.formError').hide();
        var container = this.buildErrorContainer();        
        if(data.response === "FIELDS")  {
            var formArray = $(form).serializeArray();
            $(formArray).each(function(i,item){                
                if(data[item.name] != undefined) {
                    
                    var input = $(form).find('input[name="'+item.name+'"]');
                    log($(input).next());
                    $(input).next().children().remove();
                    $(input).next().append(container).children(".formErrorContent").first().text(data[item.name]);
                    $(input).next().show();
                }                
            });                        
        }else {
            var error = $(form).find('.formError');
          
            $(error).children().remove();
            $(error).append(container).children().first().text(data.response);
            $(error).show();
            var input = $(form).find('input');
            $(input).addClass('yd-form-invalid');
        }     
          
       
         
    // log(error);
    };
   
    this.buildErrorContainer = function() {
        var container =   '<div class="formErrorContent"></div><div class="formErrorArrow"><div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div>';
        container += '<div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div></div>';
        return container;
    };
    
};

$(document).ready(function() {
    var discountInstance = new DiscountVerification();
    discountInstance.init();       
});
