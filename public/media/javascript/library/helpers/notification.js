/**
 * Notify the user
 * @author mlaug
 * @param string $type
 * - success
 * - error
 */
function notification (type, msg, perma, replace) {
    var $div = $(
        '<div class="notification rund ' + type + '">' +
        '   <a href="#" class="closeNotification"><img src="/media/images/yd-background/notification-close.png" alt="close" /></a>' +
        '   <ul><li>' + msg + '</li></ul>' +
        '</div>'
    )
    
    if(replace && $('#yd-notifications').children().length) {
        $div.replaceAll('#yd-notifications div');
    }else {
        $div.appendTo('#yd-notifications');
    }    
            
    if (perma === undefined || perma === false) {
        setTimeout(function(){
            $div.fadeOut('slow');
        }, 10000);
    }
}
$(document).ready(function(){
    if (!$("#yd-notifications").length) {
        $("body").prepend('<div id="yd-notifications"></div>');
    }
    $('div.notification').each(function(){
        var $this = $(this);
        setTimeout(function(){
            $this.fadeOut('slow');
        }, 5000);
    });
    $('a.closeNotification').live('click', function(){
        $(this).closest("div").fadeOut("fast", function(){
            $(this).remove();
        });
        return false;
    });
});