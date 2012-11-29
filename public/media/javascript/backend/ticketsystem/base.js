/**
 * pull a ticket into dash
 * @author mlaug, daniel
 * @since 21.01.2011
 */
function pullTicket(ticketId){   
    var currentTicketId = parseInt($.cookie('current-support-ticket'));
    $.ajax({
        url: '/request_administration_ticketsystem/pull/id/' + ticketId,
        success: function(html){
            havePulledOne = true;
                
            //push away an current loaded ticket             
            if ( currentTicketId !== 'NaN' && currentTicketId > 0 && ticketId != currentTicketId){   
                pushTicket(currentTicketId,undefined, true);
            }
            catchCloseClick(ticketId);
            $.cookie('current-support-ticket', ticketId);            
            $('#yd-pull-in-ticket-here').html(html);
            $('#tabs').tabs();
            $('.yd-grid-trigger').gridBox();           
            $('#yd-ticket-comment').emptytext();
        },
        error: function(){
            $('#yd-pull-in-ticket-here').html('');
        }
    });
}

/**
 * push the ticket back
 * @author mlaug, daniel
 * @since 21.01.2011
 */
function pushTicket(ticketId, command, inPull, track){
    currentlyPushing = true; //lock system
    $('#tabs').tabs('destroy');
    $('#yd-pull-in-ticket-here').html('<div class="content-warning">' + $.lang('ticket-finish') + '</div>');
    
    if (command !== 'undefined') {
        $('.content-warning').append('<br /><br />' + command);
    }
    //track defined in request_administration_ticketsystem/push
    if(track === undefined) {
        track = "0";
    }
    
    havePulledOne = false;
    $.ajax({
        url: '/request_administration_ticketsystem/push/id/' + ticketId + "/track/" + track,
        success: function (){
            //Free Window from Close Function
            if(inPull !== true) {
                $(window).unbind('beforeunload');
                $.cookie('current-support-ticket', null);         
            }
            currentlyPushing = false; //unlock system   
               
        }
    });
    
}

/**
 * create a comment
 * @author mlaug, daniel, Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 29.09.2011
 */
function createComment(comment, id, type){
    var $escaper = $('<div></div>');
    $escaper.text(comment);
    comment = $escaper.html();
    
    if (type == null || type == undefined || type == "order") {
        
        $('.yd-ticket-save-comment').prop('data-comment-lock', 1);
        $.ajax({
            type: 'POST',
            data: {
                comment: comment
            },
            url: '/request_administration_orderedit/comment/id/' + id,
            success: function() {
                $('.ticket-log-entries').prepend("<b>" + $.lang('ticket-recently') +  "</b><br />"+ $.lang('ticket-comment-text') +comment + " " + $.lang('ticket-from') + " " + $('#yd-ticket-admin').text() + "<br />");
            },
            complete: function() {
                $('.yd-ticket-save-comment').removeProp('data-comment-lock');
            }
        });
    }
    else {
        // service comment
        var allwaysCall = 0;       
        if ($('input:radio[name=yd-service-comment]:checked')[0].id == "yd-service-comment-allwayCall") {
            allwaysCall = 1;
        }
        
        $('.yd-ticket-save-comment-service').prop('data-comment-lock', 1);
        
        $.ajax({
            type: 'POST',
            url: '/request_administration_ticketsystem/comment',
            data: {
                id: id,
                allwayscall: allwaysCall,
                comment: comment
            },
            success: function() {
                $('#yd-ticket-service-comment').html("<br /><b>" + $.lang('ticket-recently') +  " </b><br />"+ $.lang('ticket-comment-text') + comment + " " +  $.lang('ticket-from') + " " +  $('#yd-ticket-admin').text());
            },
            complete: function() {
                $('.yd-ticket-save-comment-service').removeProp('data-comment-lock');
            }
        });
    }
}

/**
 * call cronjob and check for new jobs
 * there is always work to do
 * @author mlaug
 * @since 21.01.2011
 */
function callCronjob(){
    
    var that = this;
    
    if (currentlyPushing === true || currentlyCronjobbing === true) { //system is locked, currently pushing back an ticket
        currentlyPushing = false; //avoid blocks, so set this to false, 15seconds should be enough
    }
    else{
        currentlyCronjobbing = true;
        $.ajax({
            timeout: 30000,
            dataType: 'json',
            data: {
                current : $.cookie('current-support-ticket'),
                message : $.cookie('current-support-message'),
                filter : ($.cookie('current-support-filter') == null? '' : $.cookie('current-support-filter'))
            },
            url: '/request_administration_ticketsystem/cronjob',
            success: function (json){
                $('.content-right').html(json.html);
                that.statsCallViewUpdate(json.stats);
                var currentTicketId = $.cookie('current-support-ticket');
                var currentMessageId = $.cookie('current-support-message');
                if (havePulledOne === false && json.timeout < 300) {
                    if (currentTicketId > 0) {
                        pullTicket(currentTicketId);
                    }   
                    if (currentMessageId > 0){
                        pullMessage(currentMessageId);
                    }
                }
                else {
                    $('#yd-timeout').html(json.timeout);
                    if (json.timeout > 300) {
                        //trackstates defined in request_administration/orderedit
                        pushTicket(currentTicketId, 'Timeout abgelaufen.',false,2);
                    }
                }
            },
            complete: function(){
                currentlyCronjobbing = false;              
            }
        });
    }
    
    //view update für statistik
    this.statsCallViewUpdate = function(json) {
                        
        $.each(json , function(index,item) {        
            $('#'+ index).text(item)            
        });                
        $('.yd-tickets-nav').show();
        $('.stats').each(function() {
            
            var elem = $($(this).children()[0]).children()[0];           
            if(elem && elem.id == $.cookie('current-support-filter')) {
                $(this).addClass('active');
            }else {
                $(this).removeClass('active');
            }
            
            if($.cookie('current-support-filter') == null || $.cookie('current-support-filter') == undefined) {
                $('.yd-tickets-all').addClass('active');
            }
            
        })
    
    }
}

/**
 * confirm an order by given id
 * @author mlaug,daniel
 * @since 21.01.2011
 */
function confirmOrder(ticketId, callBack,track){
    
    
    //trackstates defined in request_administration/orderedit
    if(track == undefined) {
        track = 0;
    }
    
    $.ajax({
        url: '/request_administration_orderedit/confirm/id/' + ticketId + '/track/' +track,
        success: function (data){            
            if(typeof(callBack) === "function") {
                callBack();
            }
            showMessage(data);        
        }
    });
}

/**
 * mark a given order as fake
 * @author mlaug
 * @since 21.01.2011
 */
function markAsFake(ticketId){
    $.ajax({
        url: '/request_administration_orderedit/fake/id/' + ticketId,
        success: function (data){
            showMessage(data);        
        }
    });
}

/**
 * resend a given order
 * @author mlaug
 * @since 21.01.2011
 */
function resendOrder(ticketId,service,courier){
    $.ajax({
        url: '/request_administration_orderedit/resend/id/' + ticketId + '/torestaurant/' + service + '/tocourier/' + courier,
        success: function (data){
                                      
            showMessage(data);        
        }
    });
}

/**
 * cancel a given order
 * @author mlaug
 * @modified
 * @since 21.01.2011
 */
function cancelOrder(ticketId, paypal, credit, ebanking, comment, informrestaurant, informcustomer){
                
    $.ajax({
        url: '/request_administration_orderedit/storno/id/' + ticketId 
            + '/paypal/' + paypal 
            + '/credit/' + credit 
            + '/ebanking/' + ebanking 
            + '/reasonId/' + comment 
            + '/informrestaurant/' + informrestaurant 
            + '/informcustomer/' + informcustomer,            
        success: function (data){
            showMessage(data);        
        }
    });                                                
}

/**
 * @author Daniel Hahn <hahn@lieferando.de> 
 * @since 29.09.2011
 * 
 */
function showMessage(data){
    //remove old messages
    $('.notification[style="display: none;"]').each(function(index,item){
        $(item).remove();
    });
    notification('info', "<h1>" + data + "</h1>");
}

/**
 * create a new ticket in the support system
 * @author mlaug
 * @since 25.01.2011  
 */
function createOsTicket (ticketId, action) {
    osTicketId = ticketId;
    osTicketAction = action;
    $("#yd-osticket-body").val("").show().focus();
    $("a.yd-osticket-send").css('display', 'block');
}

function sendOsTicket(){
    $.ajax({
        type: 'POST',
        url: '/request_administration_ticketsystem/ticket',
        data: {
            id: osTicketId,
            command: osTicketAction,
            body: $("#yd-osticket-body").val()
        },
        success: function(){
            alert('Ticket erfolgreich erstellt');
            createComment('succesfully creating ticket ' + osTicketAction, osTicketId);
        }
    });
    $("a.yd-osticket-send, #yd-osticket-body").hide();
}

/**
 *
 * @daniel
 * @since 29.09.2011
 * catch close event and save ticket to prevent it from being lost
 */
function catchCloseClick(ticketId) {
    $(window).one('beforeunload', function(event) {
           
        pushTicket(ticketId, $.lang('ticket-no-action'));
        
        return true;
    });
}


var osTicketId, osTicketAction;
var havePulledOne = false;
var currentlyPushing = false;
var currentlyCronjobbing = false;
$(document).ready(function(){
   
    if ( $('.ticketsystem').length > 0 ){
   
        //cronjob
        callCronjob();
        setInterval(callCronjob, 60000);

        //push this ticket away
        $('.yd-ticket-push-this-ticket').live('click', function(){
            var ticketId = $(this).attr('id').split('-')[4];       
            pushTicket(ticketId, $.lang('ticket-no-action'),false,1);
        
        });

        //pull a ticket
        $('.yd-pull-this-ticket').live('click', function(){
            var ticketId = this.id.split('-')[3];
            $('#yd-pull-in-ticket-here').html('<div class="content-warning">' + $.lang('ticket-wait') + '</div>');
            pullTicket(ticketId);            
            $(this).fadeOut();
        });

        
        //confirm order, because all information have been exchanged via phone
        $('.yd-ticket-order-confirmoraly').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');   
            confirmOrder(ticketId, function(){
                createComment('(heyho) Order has been confirmed due to oral intercourse', ticketId);                
                pushTicket(ticketId, $.lang('ticket-confirm-oraly'));
            }, 1 );
            
        });

        //confirm that this order has reached the service, nevertheless retarus didn't told us so'
        $('.yd-ticket-order-confirmfax').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');
            confirmOrder(ticketId, function(){
                createComment('(heyho) Order has been confirmed, fax has reached service, fuck the fax report', ticketId);                
                pushTicket(ticketId, $.lang('ticket-confirm-fax'));
            }, 2 );               
            
        });

        //confirm this order
        $('.yd-ticket-order-confirm').live('click', function(){
            var ticketId = this.id.split('-')[2];            
            $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');
            log('hier');
            confirmOrder(ticketId, function() {
                createComment('(heyho) Order has been manually confirmed', ticketId);  
                pushTicket(ticketId, $.lang('ticket-confirm'));
            });
            
        });

        //resend this order
        $('.yd-ticket-order-resend').live('click', function(){
            var ticketId = this.id.split('-')[2];

            var service = $('#yd-order-resend-service:checked').val();
            var courier = $('#yd-order-resend-courier:checked').val();

            if (service === 'undefined' || service != 1) {
                service = 0;
            }

            if (courier === 'undefined' || courier != 1) {
                courier = 0;
            }

            $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');
            resendOrder(ticketId, service, courier);
            pushTicket(ticketId, $.lang('ticket-resend'));
        });

        //mark this order as fake
        $('.yd-ticket-order-fake').live('click', function(){
            
            var confirmation = confirm($.lang('confirm-blacklist'));            
            if(confirmation) {
                var ticketId = this.id.split('-')[2];  
                $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');
                markAsFake(ticketId);
                createComment('(heyho) Order has been blacklisted', ticketId);
                pushTicket(ticketId, $.lang('ticket-fake'));
            }                      
        });

        //cancel this order
        $('.yd-ticket-order-cancel').live('click', function(){
            var ticketId = this.id.split('-')[2];    
            
            var informCustomer = $('#yd-cancel-informcustomer:checked').val();
            if (informCustomer === 'undefined' || informCustomer != 1) {
                informCustomer = 0;
            }
            var informRestaurant = $('#yd-cancel-informrestaurant:checked').val();
            if (informRestaurant === 'undefined' || informRestaurant != 1) {
                informRestaurant = 0;
            }
            var paypal = $('#yd-paypal-cancel:checked').val();
            if (paypal === 'undefined' || paypal != 1) {
                paypal = 0;
            }
            
            var credit = $('#yd-credit-cancel:checked').val();
            if (credit === 'undefined' || credit != 1) {
                credit = 0;
            }
           
            var ebanking = $('#yd-ebanking-cancel:checked').val();
            if (ebanking === 'undefined' || ebanking != 1) {
                ebanking = 0;
            }
           
            var reason = $('#yd-orderedit-storno-reason').val();
            if ( reason === 'undefined' || reason.length == 0 ){
                alert($.lang('ticket-reason'));
                return false;
            }
           
            $('.yd-ticket-action').html('<div class="content-warning">'+ $.lang('ticket-action-wait') +  ' </div>');

            cancelOrder(ticketId, paypal, credit, ebanking, reason, informRestaurant, informCustomer);
            pushTicket(ticketId, $.lang('ticket-cancel'));

        });

        //create a comment
        $('.yd-ticket-save-comment').live('click', function(){
                        
            if($(this).prop('data-comment-lock') == 1) {
                log('locked');
                return false;
            }                        
            
            var ticketId = this.id.split('-')[3];
            var comment = $('textarea#yd-ticket-comment').val();

            if (comment.length == 0 || comment == $.lang('ticket-comment-default')) {
                alert($.lang('ticket-comment'));
                return false;
            }
                                  
            createComment(comment, ticketId);
        });
        
        //create a comment for service
        $('.yd-ticket-save-comment-service').live('click' ,function(){
            
             if($(this).prop('data-comment-lock') == 1) {
                log('locked');
                return false;
            }      
            
            
            var radio = $('input:radio[name=yd-service-comment]:checked');
            if(radio.length > 0) {
                var commentService = radio.val();
                var time = $('input[name=yd-service-comment-time]').val();
                var text = $('textarea[name=yd-service-comment-text]').val();
                var ticketId = this.id.split('-')[4];
                if(time.length > 0 && radio[0].id == "yd-service-comment-until") {
                    commentService = commentService +" " +time;
                }else if(text.length > 0 && radio[0].id == "yd-service-comment-text-other") {
                    commentService = text;
                }                                    
                if(commentService.length > 0) {
                    createComment(commentService, ticketId, "service")              
                }
            }
        });
           
        $('#yd-service-comment-text-other').live('click',function(){        
            var textarea = $('#yd-service-comment-text');
            textarea.prop("disabled", false);
            textarea.text('');
            textarea.focus();           
        });
           
           
        //trigger to create some os tickets
        $('a.yd-osticket-send').live('click', function(){
            sendOsTicket();
            this.blur();
            return false;
        });

        $('a.yd-ticket-changecard').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $(this).remove();
            createOsTicket(ticketId, 'changecard');
            this.blur();
            return false;
        });

        $('a.yd-ticket-location').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $(this).remove();
            createOsTicket(ticketId, 'location');
            this.blur();
            return false;
        });

        $('a.yd-ticket-bill').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $(this).remove();
            createOsTicket(ticketId, 'bill');
            this.blur();
            return false;
        });

        $('a.yd-ticket-payment').live('click', function(){
            var ticketId = this.id.split('-')[2];
            $(this).remove();
            createOsTicket(ticketId, 'payment');
            this.blur();
            return false;
        });       
        
        
        $('.yd-tickets-nav').on('click','.stats', function(){
                                   
            var elem = $($(this).children()[0]).children()[0];
                                   
            if(elem && elem.id == 'All')  {
                $.cookie('current-support-filter', null);                
            }else {
                $.cookie('current-support-filter', elem.id);
            }
            callCronjob();
            
        })
        
    }
});
