/**
 * @author vpriem
 * @since 05.04.2012
 */
var _gaq = _gaq || [];

$(document).ready(function(){
   
    if (!$('.yd-service-page').length) {
        return;
    }
   
    /**
     * @author vpriem
     * @since 05.04.2012
     */
    var GAServiceCountOnlineOpen = 
        GAServiceCountOnlineClosed = 
        GAServiceCountOffline = 0;
    
    $('.yd-service-container:visible').each(function(){
        if ($('.service-offline', this).length) {
            GAServiceCountOffline++;
        }
        else if ($(this).data('open') === true) {
            GAServiceCountOnlineOpen++;
        }
        else {
            GAServiceCountOnlineClosed++;
        }
    });
    
    _gaq.push(['_setCustomVar', 1, 'service_count_online_open', "" + GAServiceCountOnlineOpen, 2]);
    _gaq.push(['_setCustomVar', 2, 'service_count_online_closed', "" + GAServiceCountOnlineClosed, 2]);
    _gaq.push(['_setCustomVar', 3, 'service_count_offline', "" + GAServiceCountOffline, 2]);

});