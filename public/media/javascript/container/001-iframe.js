/**
 * @author mlaug
 */
function loadIframe(child_url, id, coop, include_url){
        
    var plz = getUrlVars()['plz'];
    var current_height = 700;

    var parent_url = encodeURIComponent(document.location.href);
    
    if (typeof plz === "undefined") {
        log('no plz defined, loading iframe');
        $('#yd-the-iframe').html('<iframe marginheight="0" marginwidth="0" frameborder="0" src="http://' + include_url + '/if/' + coop + '/1/start#' + parent_url + '" id="' + id + '" style="width:100%; height:' + current_height + 'px; border:0px;" ></iframe>');
    }
    else {
        log('plz defined, loading iframe');
        var url = 'http://' + include_url + '/if/' + coop + '/1/service/?plz=' + plz + '#' + parent_url;
        $('#yd-the-iframe').html('<iframe marginheight="0" marginwidth="0" frameborder="0" src="' + url + '" style="width:100%; height:' + current_height + 'px; border:0px;" id="' + id + '" ></iframe>');
    }
         
    $.receiveMessage(function(e){
        var rec_height = Number( e.data.replace( /.*if_height=(\d+)(?:&|$)/, '$1' ) );
        if ( !isNaN( rec_height ) && rec_height > 0 && rec_height !== current_height ) {  
            current_height = rec_height;
            log('received new height ' + rec_height + ', adjusting height');
            $('#' + id).height(rec_height);
        }
    });
}