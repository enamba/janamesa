/**
 * the piwik tracker is defined in the js-packager.php while compiling
 * since the id is stored in the application.ini
 * @see /public/js-packager.php
 */


/**
 * set a cookie for internal tracking
 * @author mlaug
 * @since 29.03.2011
 */
function setCookieForSaleChannels(channel, subchannel, value){      
    var params = getUrlVars();     
    
    if ("yd_com" in params){
        var com_id = params['yd_com'];
        log('found yd_com link for url ' + com_id + ', writing cookie');
        $.cookie('yd-channels', $.base64.encode(JSON.stringify({
            com_id: com_id
        })), {
            path: '/',
            expires: 60 // 2 month, please edit in php too
        });
        return true;
    }              
    
    if ("utm_campaign" in params || "pk_campaign" in params) {
        $.cookie('yd-channels', null);
        return true;
    }
}

$(document).ready(function(){
    setCookieForSaleChannels();
});