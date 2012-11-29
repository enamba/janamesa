$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 23.07.2012
     */
    var $div = $('.yd-order-notify').bind("refresh", function(){
        var hash = this.id.split('-')[3];
        if (!hash.length) {
            return;
        }
        
        var $this = $(this);
        $.ajax({
            url: '/request_order/checknotify',
            data: {
                hash: hash
            },
            success: function (resp) {
                $this.html(resp);
                if ($this.find(".yd-order-notify--22").length) {
                    $("#yd-rest-info-tel").hide();
                } else {
                    $("#yd-rest-info-tel").show();
                }
            }
        });
    });
    if (!$div.length) {
        return;
    }
    var times = 0;
    var timer = setInterval(function(){
        $div.trigger("refresh");
        times++;
        if (times > 10) {
            clearInterval(timer);
        }
    }, 6000);
});
