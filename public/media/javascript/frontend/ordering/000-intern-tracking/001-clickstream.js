$(document).ready(function(){  
    var track = $.cookie('yd-track');
    if ( track ){
        $.cookie('yd-track',null);
    }
    /*if ( !track ){
        //mark first hit
        track = new Date().getTime();
    }
    $.cookie('yd-track',track + '||' + $.base64.encode(new Date().getTime()/1000 + '::' + document.location.href), {
        path : '/',
        expires : 365
    });*/
});