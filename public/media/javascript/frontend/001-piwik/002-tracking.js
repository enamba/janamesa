/**
 * the piwik tracker is defined in the js-packager.php while compiling
 * since the id is stored in the application.ini
 * @see /public/js-packager.php
 */

/**
 *_piwikUrl is set in js-packer.php via application.ini
 */
var pkBaseURL = (("https:" == document.location.protocol) ? "https://" + _piwikUrl : "http://" + _piwikUrl);
var _paq = _paq || [];

try {
    if ( !piwikId ){
        log("no piwikId defined");
    }
    else{
        (function(){
            log("using piwik id " + piwikId);
            _paq.push(['setSiteId', piwikId]);
            _paq.push(['setTrackerUrl', pkBaseURL+'piwik.php']);
            _paq.push(['enableLinkTracking']);
            log('successfully loading piwik tracker');
        })();
        
    }
} catch( err ) {
    log('failed to load piwik tracker' + err);
}

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