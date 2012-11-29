

/**
 * Trigger Handling for Menu Page
 * @author dhahn
 * @since 10.05.2012
 */
var MenuTrigger = function() {
    
    var $this = this;
    var serviceId =  services[0].id.toString();
    var isServiceOnline = true;
    var isPlzSet = false;
    var ydServiceInstance = new YdService(serviceId);     
     
    this.init = function() {

        isServiceOnline = this.isServiceOnline();
         
        if (isServiceOnline) {
            this.checkIsOpen();
            this.checkIsClosing();
        }
        
        var cityId = null;
        if (typeof ydState !== "undefined") {
            cityId = ydState.getCity();
        }
        if (cityId !== null) {
            if (ydState.setCity(cityId) !== false) {
                isPlzSet = true;
            }
        }
        
        if (!isPlzSet) {
            ydOrder.clear_bucket();

            // update view to show lowest deliver cost and so
            var lowestDeliverCost = 1000000;
            var lowestMinAmount = 1000000;
            for (var prop in ydCurrentRanges) {
                if (ydCurrentRanges[prop].deliverCost < lowestDeliverCost) {
                    lowestDeliverCost = ydCurrentRanges[prop].deliverCost;
                }
                if (ydCurrentRanges[prop].minCost < lowestMinAmount) {
                    lowestMinAmount = ydCurrentRanges[prop].minCost;
                }
            }
            
            ydOrder.set_deliver_cost(lowestDeliverCost, 0)
                   .set_min_amount(lowestMinAmount);
                   
            // add some extra wording
            $('.yd-deliver-cost').html($.lang("from-currency", int2price(lowestDeliverCost)));
            $('.yd-min-amount').html($.lang("from-currency", int2price(lowestMinAmount)));
        }
    };
            
    /**
     * Menu Page
     * If service is called by direct link and is offline, 
     * a lightbox appears and informs the customer
     * @author vpriem, dhahn
     * @since 10.05.2012
     * @return boolean
     */
    this.isServiceOnline = function() {
        
        var isOnline = true;
        
        $("#yd-service-offline").each(function(){
            isOnline = false;
        
            $(this).dialog({
                width: 600,
                height: 380,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                },
                beforeClose: function(){
                    historyBack();
                    return false;
                }
            });
        });
        
        return isOnline;
    };
    
    /**
     * Menu Page
     * If serice called directly
     * show a lightbox to invite user to choose his plz
     * @author vpriem, dhahn
     * @since 10.05.2012
     * @return boolean
     */
    this.isPlzSet = function(callback) {
        
        if (isServiceOnline && !isPlzSet) {
            $("#yd-select-plz-form").submit(function(e){
                e.preventDefault();
                $this.handlePlzSubmit(this, callback); 
            });
            $("div.yd-select-plz").each(function(){
                $(this).dialog({
                    width: 600,
                    height: 380,
                    modal: true,
                    close: function(e, ui) {
                        $(ui).dialog('destroy');  
                    },
                    beforeClose: function() {
                        if (!ydState.getCity()) {
                            historyBack();
                            return false;
                        }
                    }
                });
                if ($('.pl-autocomplete-wrapper', this).length) {
                    // A hack for Poland - retrieving first range's city and insert into form
                    for (var zone in ydCurrentRanges) {
                        var $cityInput = $('.pl-autocomplete-city', this);
                        // First 6 characters are zip code (with space)
                        $cityInput.val(ydCurrentRanges[zone].name.substr(6));
                        setTimeout(function () {
                            $cityInput.trigger('focusout');
                        }, 250);
                        $('.pl-autocomplete-street', this).select();
                        break;
                    }
                }
            });
            
            return false;
        }
        
        return true;
    };
    
    /**
     * Check if Service is Open
     * @author dhahn
     * @since 10.05.2012
     * @return boolean
     */
    this.checkIsOpen = function() {
                                             
        log("checkIsOpen: Service IsOpen: "+  ydServiceInstance.isOpen());         
                
        if (!ydServiceInstance.isOpen()) {
                           
            $('.yd-menu-detail-open').css('color', 'red');
             
            var preorder =  $.base64.decode($.cookie('yd-preorder') ? $.cookie('yd-preorder') : 0) ;
                
            log('checkIsOpen:  Preoder Cookie: ' + preorder);     
                
            if (preorder != serviceId) {            
                var type = ydServiceInstance.type;
                var url = '/request_service/preorder/type/' + type;     
                
                if (type != 'rest') {                    
                    var text = $('.yd-menu-handling-time').text();
                    url +=  "/handlingtime/"+ escape(text); 
                }    
                                                                     
                openDialog(url, {
                    width: 600,
                    height: 400,
                    modal: true,
                    close: function(e, ui) {
                        $(ui).dialog('destroy');
                    },
                    beforeClose: function() {
                        var preorder = $.base64.decode($.cookie('yd-preorder') ? $.cookie('yd-preorder') : "" );
                        log("Preorder Cookie: "+ preorder);
                        if (preorder != serviceId) { 
                            log("Close Dialog with no Preorder: " + preorder);
                            historyBack();
                        }
                        return false;
                    }
                }, $this.handleIsOpenClick);
                
                return false;
            }
                          
        }
        
        return true;                         
    };
    
    /**
     * Check if service is closing soon and show warning lightbox
     * @author dhahn
     * @since 20.05.2012   
     * @return boolean  
     */
    this.checkIsClosing = function() {
        
        var minutes = 15;
        
        if (ydServiceInstance.isClosing(minutes * 60)) {
            log('checkIsClosing: is Closing in Minutes');
            
            $("#yd-service-closing").each(function(){
                $(this).dialog({
                    width: 400,
                    height: 380,
                    modal: true,                
                    open: $this.handleClosingOkClick,
                    close: function(e, ui) {
                        $(ui).dialog('destroy');
                    }
                });
            });
            
            return true;
        }
        
        return false;
    };
    
    /**
     * Setting cityId wenn submiting the plz lightbox 
     * @author vpriem, dhahn
     * @since 10.05.2012
     * @return boolean
     */
    this.handlePlzSubmit = function(form, callback) {
          
        var cityId = $(form.cityId).intVal();
        if (cityId > 0) {
            if (typeof ydState !== "undefined") {
                // be sure it is correctly setted
                if (ydState.setCity(cityId) === false) {
                    return false;
                }
                
                isPlzSet = true;
                
                //tracking for sociomantic in menu when ydState.getCity is not set
                //@author Daniel Hahn <hahn@lieferando.de>
                if (typeof track_sociomantic_in_menu  == "function") {
                    try {
                        track_sociomantic_in_menu();
                    } catch(err) {
                    }
                }
                if (typeof track_adlantic_in_menu  == "function") {
                    try {
                        track_adlantic_in_menu();
                    } catch(err) {
                    }
                }
                          
                closeDialog(true);
                
                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
        
        return false;
    };
    
    /**
     * Click Handler for preorder
     * @author dhahn
     * @since 10.05.2012
     */
    this.handleIsOpenClick = function() {
        $("#yd-yes-i-want").click(function() {         
            $.cookie('yd-preorder', $.base64.encode(serviceId));                 
            closeDialog(true);
            return false;
        });
    };
    
    
    this.handleClosingOkClick = function() {
        $('#yd-service-closing-ok').click(function(){           
            $("#yd-service-closing")
            .dialog('close');
            return false;
        });
    };
    
};

var ydMenuTrigger = null;
$(document).ready(function(){
    
    /**
     * Menu Page
     * @author dhahn
     */
    if (typeof services != 'undefined' && services.length) {      
        $('.yd-menu-modern, div#yd-menu').each(function(){
            ydMenuTrigger = new MenuTrigger();
            ydMenuTrigger.init();
        });
    }

    /**
     * Finish Page
     * @author mlaug
     * check for current order state and remove elements according
     */
    var kindToRemove = 'comp';
    if (ydState.getKind() == 'comp') {
        kindToRemove = 'priv';
    }
    
    $('.yd-only-' + kindToRemove).each(function(){
        log('deactivating ' + this.tagName + ' element due to private order state');
        if (this.tagName == 'INPUT' || this.tagName == 'SELECT') {
            $(this).prop('disabled', true);
        }
        else {
            $(this).hide();
        }
    });
    
});
