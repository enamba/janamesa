// check user and try to assign new budget
$("#yd-add-budget").live('click',function() {
    
    var check = true;
    $('#yd-loading-budget').show();
    
    $('.budget-input').css('border','1px solid #CCCCCC');

    var email = $('#check_email');
    var euro = parseInt($('#check_amount_euro').val(),10);
    var cent = parseInt($('#check_amount_cent').val(),10);
    var code = $('#projectcode');
    var addition = $('#check_project_addition').val();
    var addition2 = $('#check_project_addition2').val();
        
        
    /**
     * company special for bbdo
     */
    if($('#check_project_addition_for').length > 0){
        if($('#check_project_addition_for').val() == 'bbdo'){
            addition = '/' + addition + '/' + addition2;
            
            if ( $('#yd-comp-need-code').length > 0){
                if(addition === ''){
                    $('#check_project_addition').css('border', '1px solid red');
                    check = false;
                }
                if(addition2 === ''){
                    $('#check_project_addition2').css('border', '1px solid red');
                    check = false;
                }
            }
        }
    }
    
    /**
     * company special for scholz-hh
     */
    if( $('#yd-budget-box').hasClass('scholz-hh')){
        if( code.val().length != 5 ){
            code.css('border','1px solid red');
            notification('warn','Bitte geben Sie genau 5 Zeichen ein.');
            check = false;
        }
    }
    
    /**
     * company special for scholz & friends berlin
     */
    if( $('#yd-budget-box').hasClass('scholz-bln')){
        //nothing to do here
    }

    if(email.val().length < 5){
        $('#check_email').css('border', '1px solid red');
        check = false;
    }        

    if ( $('#yd-comp-need-code').length > 0 && code.val().length == 0){
        code.css('border','1px solid red');
        check = false;
    }
        
    //check numbers
    if ( isNaN(euro) ){
        euro = 0;
    }
    if ( isNaN(cent) ){
        cent = 0;
    }

    var amount = (euro * 100) + cent;
    if ( amount === 0 ){
        $('#check_amount_euro').css('border', '1px solid red');
        $('#check_amount_cent').css('border', '1px solid red');
        check = false;
    }
    
    if(!check){
        $('#yd-loading-budget').hide();
        return false;
    }

    $.ajax({
        type : "POST",
        url: "/request_order/addbudget",
        data: ({
            'email'     : email.val(),
            'amount'    : amount,
            'customer'  : $('#yd-customerId').val()
        }),
        success : function(){            
            ydBudget.add(email.val(),amount,code.val(),addition);   
        },
        //maybe a 404 or 406 if customer not exists or amount is not covered
        error : function(){
            notification('warn','Konnte Budget nicht hinzufügen');
        }
        
    });
    
    $('#yd-loading-budget').hide();
    return false;
});

//remove item from share
$('.yd-budget-erase').live('click',function(){
    var hash = this.id.split('-')[3];
    log('removing budget with hash ' + hash);
    ydBudget.remove(hash);
    return false;
});