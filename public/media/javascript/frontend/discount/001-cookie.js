/**
 * set a cookie for internal tracking
 * @author mlaug
 * @since 29.03.2011
 */
function setCookieForDiscount(){
    var params = getUrlVars();

    if ("disha" in params) {
        var rabattHash = params['disha'];
        log('found rabattHash  ' + rabattHash + ', requesting random discount code');

        $.ajax({
            type: "post",
            url: "/request_discount/randomcodefromdiscount/",
            data: 'rabattHash=' + rabattHash,
            success: function(response) {
                var data = $.parseJSON(response);

                if (data.status == 'OK') {
                    $.cookie('yd-rabatt-code', $.base64.encode(data.rabattCode), {
                        path: '/',
                        expires: 1
                    });
                    log('got random discount code and marked it reserved ' + data.rabattCode + ', written to cookie');
                } else {
                    log('discount is either invalid or not found.');
                }
            }
        });
    }

    if ("rabatt_code" in params){
        var code = params['rabatt_code'];
        log('found rabatt_code  ' + code + ', writing cookie');
        $.cookie('yd-rabatt-code', $.base64.encode(code), {
            path: '/',
            expires: 1 // exit when browser closed
        });
    }


    if(document.referrer.length != 0 && document.referrer.split('?')[0].indexOf(window.location.host) === -1 ) {
           $.cookie('yd-rabatt-code', null);
    }

    if($('#yd-success-page').length) {
          $.cookie('yd-rabatt-code', null);
    }

}

$(document).ready(function(){
    setCookieForDiscount();

    if($('#discount-code').length && $.cookie('yd-rabatt-code')) {
        var code = $.base64.decode($.cookie('yd-rabatt-code'));

        log('using yd-rabatt-code ' + code);
        $('#discount-code').val(code);

    }

});