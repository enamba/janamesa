$(document).ready( function () {
    var ydABTestGroup,ydABTestGroupName,i,x,y,ARRcookies=document.cookie.split(/\s*;\s*/);
    ydABTestGroupName = 'ab_colorchange'
    if(document.cookie.indexOf('yd-abtest=A') != -1) {
        ydABTestGroup="A";
    } else if(document.cookie.indexOf('yd-abtest=B') != -1) {
        ydABTestGroup="B";
    }
    if (!ydABTestGroup) {
        if(Math.random() < 0.5) {
            ydABTestGroup = 'A';
        } else {
            ydABTestGroup = 'B';
        }
        var exdate=new Date();
        document.cookie="yd-abtest=" + ydABTestGroup + "; expires="+exdate.toUTCString() + "; path=/";     
    }
    log('AB TEST');

    if (ydABTestGroup == 'B') {
        $('INPUT[name="BUSCAR RESTAURANTES"]').val("FAZER O PEDIDO");
        $('INPUT[name="BUSCAR RESTAURANTES"]').css("padding-left", 0);
    } else {
        $('INPUT[name="BUSCAR RESTAURANTES"]').val("BUSCAR RESTAURANTES");
        
    }
})