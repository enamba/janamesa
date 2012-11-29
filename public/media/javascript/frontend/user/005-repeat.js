var ydUserOrderRepeat = function() {
    
    var $this = this;
    
    var $json = null;
    
    this.init = function() {
     
        $('body').on('click', "a.yd-link-repeat-fav, a.yd-link-repeat-rest, a.yd-link-repeat-lastOrder, div.yd-service-select-lastorder", $this.load );
                     
    };
    
    this.load = function(elem) {       
        log('hier');
        $elem = $(this);
        var hash = this.id.split('-')[3];
        if(this.id.indexOf('yd-repeat-lastorder') >= 0 && $('.yd-repeat-loading').length > 0 ){
            $('.yd-link-repeat-lastOrder').hide();
            $('.yd-repeat-loading').show();
        } else if ( $('.yd-repeat-loading').length > 0 ){
            log($elem);
            $elem.hide();
            $('.yd-repeat-loading').show();
        }
        
        var location = 0;
        if($('span.yd-adress.active').length > 0){
            location = $('span.yd-adress.active').attr('id').split('-')[2];
        }
        
        $.ajax({
            url: '/request_order/repeat',
            data: {
                hash: hash,
                location: location
            },
            dataType: 'json',
            success: function (json) {
                                
                if(json.result !== undefined && json.result == false){
                    notification('error', json.msg);
                    $elem.hide();
                    $('.yd-repeat-loading').hide();
                    return false;
                }                                 
                var url = '/order_private/finish';
                if (json.kind == 'comp'){
                    $json = json;
                    $this.showLocationBox();
                   
                }else{
                    post_to_url(url, json);
                }           
            }
        });
        
        return false;
    };
    
    
    this.showLocationBox= function() {
        
        openDialog("/request_user_location/get", {
            width: 800,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
           
        }, function() {
            $("#yd-repeat-order-company").on('click', $this.submitCompanyOrder);
        });
        
    };
    
    this.submitCompanyOrder = function() {
        url = '/order_company/finish';
        $('#yd-repeat-order-errorCity').hide();
        if($('.yd-adress.active').length > 0) {
            var locationId  = $('.yd-adress.active')[0].id.split('-')[2];
            var cityId = $('.yd-adress.active')[0].id.split('-')[3];
            if(cityId == $json.cityId) {
                if (typeof ydState !== "undefined") {
                    if (locationId !== undefined) {
                        ydState.setLocation(locationId);
                    }
                    if (cityId !== undefined) {
                        ydState.setCity(cityId);
                    }
                }
            
                $json.street= null;
                $json.hausnr = null;
                $json.plz = null;
                $json.comment = null;
                $json.etage = null;            
                post_to_url(url, $json);
            }else {
                $('#yd-repeat-order-errorCity').show();
            }
            
        }
        
        
    //   post_to_url(url, $json);
    };
    
};

$(document).ready(function(){
    
    var userRepeat = new ydUserOrderRepeat();
    
    userRepeat.init();
    
    
/**
     * Repeat order
     * get json of information from ajax and post this to the finish page
     * @author mlaug
     * @since 18.08.2011
     */
//    $("a.yd-link-repeat-fav, a.yd-link-repeat-rest, a.yd-link-repeat-lastOrder").live('click', function(){
//        /**
//         * @todo use hash instead of id
//         */
//        
//        $this = $(this);
//        var hash = this.id.split('-')[3];
//        if ( $('.yd-repeat-loading').length > 0 ){
//            $this.hide();
//            $('.yd-repeat-loading').show();
//        }
//        
//        var location = 0;
//        if($('span.yd-adress.active').length > 0){
//            location = $('span.yd-adress.active').attr('id').split('-')[2];
//        }
//        
//        $.ajax({
//            url: '/request_order/repeat',
//            data: {
//                hash: hash,
//                location: location
//            },
//            dataType: 'json',
//            success: function (json) {
//                if(json.result !== undefined && json.result == false){
//                    notification('error', json.msg);
//                    $this.hide();
//                    $('.yd-repeat-loading').hide();
//                    return false;
//                }
//                var url = '/order_private/finish';
//                if (json.kind == 'comp'){
//                    url = '/order_company/finish';
//                }
//            //   post_to_url(url, json);
//            }
//        });
//        
//        return false;
//    });
//    
});