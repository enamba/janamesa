$(document).ready(function(){

    /**
     * get ratings of one service
     * @author mlaug
     * @since 04.01.2011
     */
    $('.yd-rated').live('click',function(){

        // check if offline, then show lightbox
        $form = $(this).closest('form');
        if ($($form).hasClass('service-offline')) {
            openDialog('/request_service/offline', {
                width: 600,
                height: 400,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                }
            });
            return false;
        }

        var rid = this.id.split('-')[2];
        if (rid !== undefined) {
            openDialog('/request_service/rating/id/' + rid + '/mode/' + ydState.getMode(), {
                width : 600,
                height: 380,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                }
            });
        }

        this.blur();
        return false;
    });

    /**
     * Triggered by click on the service link
     * @author vpriem
     * @since 04.01.2012
     */
    $('.yd-service-submit').live("submit", function() {
        var id = this.id.split("-")[3];
        var type = this.id.split("-")[4];
        var $form = $(this);
        
        // check if offline, then show lightbox
        if ($form.hasClass('service-offline')) {
            openDialog('/request_service/offline', {
                width: 600,
                height: 400,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                }
            });
            return false;
        }

        // check if open, then show lightbox
        var service = new YdService(id, type);
        
        if(type == 'cater' || type == 'great' ){
            var handlingTime = $(this).children('ul').children('.yd-handling-time').attr('data-handling-time');
            type += '/handlingtime/' + handlingTime;
        }
        
        if (!service.isOpen()) {
            openDialog('/request_service/preorder/back/0/type/' + type, {
                width: 600,
                height: 400,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                }
            }, function(){             
                $("#yd-yes-i-want").click(function() {
                    $.cookie('yd-preorder', $.base64.encode(id));
                    window.location.href = $form.attr("action");
                    return false;
                });
            });
            return false;
        }
    });

    /**
     * Click on the service otr service link
     * @author vpriem
     * @since 04.01.2012
     */
    $('.yd-service-select').live('click', function(){
        $(this).closest('form').submit();
        return false;
    });
    
    /**
     * show / hide special comment for service on mouseover / -out
     * @todo: this can be done with toggle!
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 28.02.2011
     */
    $('.special-comment').live('mouseover', function(){
        $(this).children('.special-comment-full').show();
    });
    $('.special-comment').live('mouseout', function(){
        $(this).children('.special-comment-full').hide();
    });

    /**
     * @view order/basis/service.htm
     * @author mlaug
     * @since 20.11.2011
     */
    $('.yd-service-info').live('hover',function(){
        var $this = $(this);
        if ( !$this.data('loaded') ){
            $this.data('loaded',true);
            $.ajax({
                url : '/request_order/serviceinfo/id/' + this.id.split('-')[3],
                success : function(resp){
                    $this.prepend(resp);
                }
            });
        }
    });
});