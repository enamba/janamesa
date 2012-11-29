/**
 * @author vpriem
 * @modified 23.11.2011 Matthias Laug <laug@lieferando.de>
 * @since 22.11.2011
 */
function pullMessage(messageId){   
    $('#yd-pull-in-ticket-here').html('<div class="content-warning">... Bitte warten ...</div>');
    $.ajax({
        url: '/request_administration_ticketsystem_message/index/mid/' + messageId,
        success: function(html){    
            havePulledOne = true;
                      
            //set current message
            currentMessage = messageId;
            $.cookie('current-support-message', messageId);
            
            $('#yd-pull-in-ticket-here').html(html);
            
            $('#yd-pull-in-ticket-here .yd-message-callback').each(function(){
                var a = this;
                $('<div></div>').load(this.href, function(){
                    $(a).after(this)
                    .remove();
                        
                    $("form.yd-message-callback-post", this).ajaxForm({
                        success: function(data) {
                            if (data.success) {
                                notification("success", data.success);
                                pushMessage(messageId);
                            }
                            if (data.error) {
                                notification("success", data.error);
                            }
                        },
                        dataType: "json"
                    })
                });
            });
            
        },
        error: function(jqXHR, textStatus, errorThrown){
            if (textStatus == 409) {
                $('#yd-pull-in-ticket-here').html('Ticket wurde schon genommen');
            }
        }
    });
}

/**
 * push the ticket back
 * @author mlaug, daniel
 * @since 21.01.2011
 */
function pushMessage(messageId) {
    currentlyPushing = true; // lock system
    $('#yd-pull-in-ticket-here').html('<div class="content-warning">Nachricht wurde bearbeitet oder zurückgegeben.</div>');
    
    $.ajax({
        url: '/request_administration_ticketsystem_message/push/mid/' + messageId,
        success: function(){
            havePulledOne = false;
            currentlyPushing = false; // unlock system
            $.cookie('current-support-message', null); 
        }
    });
}

/**
 * push the ticket back
 * @author vpriem
 * @since 21.01.2011
 */
function closeMessage(messageId) {
    currentlyPushing = true; // lock system
    $('#yd-pull-in-ticket-here').html('<div class="content-warning">Nachricht wurde bearbeitet oder zurückgegeben.</div>');
    
    $.ajax({
        url: '/request_administration_ticketsystem_message/close/mid/' + messageId,
        success: function(){
            havePulledOne = false;
            currentlyPushing = false; // unlock system
            $.cookie('current-support-message', null); 
        }
    });
}

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 22.11.2011
 */
var currentMessage = null;

$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 22.11.2011
     */
    $('.yd-pull-this-message').live('click', function(){
        var messageId = this.id.split('-')[3];
        pullMessage(messageId);
        $(this).fadeOut(400, function(){
            $(this).remove();
        });
    });
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    $('.yd-message-release').live('click', function(){
        var messageId = this.id.split('-')[3];
        pushMessage(messageId);
    });
    
    /**
     * @author vpriem
     * @since 21.02.2012
     */
    $('.yd-message-to-ticket').live('click', function(event){
        event.preventDefault();
        
        var messageId = this.id.split('-')[3];
        var ticketId = this.id.split('-')[4];
        closeMessage(messageId);
        pullTicket(ticketId);
    });
});