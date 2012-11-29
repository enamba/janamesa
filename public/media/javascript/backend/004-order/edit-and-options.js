


$(document).ready(function(){
    
    //show popup with help descriptions to the order
    $('.yd-help-order-options').live('click', function(){
        var id = $(this).attr('data-id');
        openDialog('/request_administration/orderhelp/id/' + id);
        return false;
    });
    
    /**
     * ORDER EDIT
     */
          
    //default options for ajaxForm and $.ajax
    var optionsForOrderEditDialog = {
        
        //ajaxFrom
        beforeSubmit : function(){
            $('#dialog .yd-order-edit-dialogs .be-dialogs-body').html('<div class="yd-dialog-in-progress">... WORKING ...</div>');
        },
        
        //ajax call
        beforeSend : function(xhr){
            xhr.setRequestHeader("YourdeliveryDoormen", "kjfsdkjhdfsalkhjfdsalkhjfdas");
            $('#dialog .yd-order-edit-dialogs .be-dialogs-body').html('<div class="yd-dialog-in-progress">... WORKING ...</div>');
        },
        
        success : function(responseText){
            $('#dialog .yd-order-edit-dialogs .be-dialogs-body').html('<div class="yd-dialog-in-progress">' + responseText + '</div>');
            setTimeout(function(){
                location.reload();
            }, 2000);
        },
        error : function(xhr){
            if ( xhr.status == "404" ){
                log('could not find order');
            }
            if ( xhr.status == "406" ){
                log('could not validate data');
            }
            $('#dialog .yd-order-edit-dialogs .be-dialogs-body').html('<div class="yd-dialog-in-progress">' + xhr.responseText + '</div>');
        }
    }
                 
    //show popup with orders edit options
    $('.yd-edit-order-options').live('click', function(){
        var id = $(this).attr('data-id');
        openDialog('/request_administration_orderedit/index/id/'+id, {
            close: function(event, ui) {     
                
            }
        }, function(){
            
            //storno order form
            var stornoOptions = $.extend({}, optionsForOrderEditDialog);
            $.extend(stornoOptions,{
                'beforeSubmit' : function(){
                    var reason = $('#reasonId').val();
                    if ( reason === 'undefined' || reason.length == 0 || reason == 0){
                        alert($.lang('ticket-reason-lightbox'));
                        return false;
                    }
                    $('#dialog .yd-order-edit-dialogs .be-dialogs-body').html('<div class="yd-dialog-in-progress">... WORKING ...</div>');
                }
            });
            
            $('#yd-order-edit-storno form', this).ajaxForm(stornoOptions);
            
            //confirm order form
            $('#yd-order-edit-confirm form', this).ajaxForm(optionsForOrderEditDialog);
            
            //paypal whitelist and blacklist
            $('#yd-order-edit-paypal form', this).ajaxForm(optionsForOrderEditDialog);
            
            //resend order
            $('#yd-order-edit-resend form', this).ajaxForm(optionsForOrderEditDialog);          
                      
            //comment an order
            $('#yd-order-edit-comment form', this).ajaxForm(optionsForOrderEditDialog);
            
            //change payment
            $('#yd-order-edit-change-payment form', this).ajaxForm(optionsForOrderEditDialog);
            
        });
        return false;
    });
        
    // resend email for order rating
    $('.yd-order-ratingemail').live('click',function(){
        var id = $(this).attr('id').split('-')[3];
        $.ajax($.extend(optionsForOrderEditDialog, {
            url : '/request_administration_orderedit/ratingemail/id/' + id
        }));
        return false;
    });

    // resend order confirmation email
    $('.yd-order-confirmationemail').live('click',function(){
        var id = $(this).attr('id').split('-')[3];
        $.ajax($.extend(optionsForOrderEditDialog, {
            url : '/request_administration_orderedit/confirmationemail/id/' + id
        }));
        return false;
    });
            
    //mark order as fake
    $('.yd-order-fake').live('click',function(){
        if ( confirm($.lang('confirm-blacklist-lightbox')) ){           
            var id = $(this).attr('id').split('-')[3];
            $.ajax($.extend(optionsForOrderEditDialog, {
                url : '/request_administration_orderedit/fake/id/' + id 
            }));
        }
    });
    
});