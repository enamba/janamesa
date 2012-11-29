$(document).ready(function(){

    /**
     * Select address for ordering
     * @author vpriem
     * @since 10.02.2011
     */
    $('span.yd-adress').live('click', function(){
        var locationId = this.id.split('-')[2];
        var cityId = this.id.split('-')[3];
        
        $('span.yd-adress').removeClass('active');
        $(this).addClass('active');
        $('#yd-start-order-from-address')
            .data('href', $(this).attr('data'))
            .data('city', cityId)
            .data('location', locationId);
    })
    .filter(".active")
    .click();
    
    /**
     * TODO: should be moved to in base.js
     * Error Message with hashtag
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.09.2011
     */
    var hash = location.hash;
    if(hash !== undefined && $.lang(hash.substring(1)).length != 0) {      
        notification("error", $.lang(hash.substring(1)), false);
    }
    
    /**
     * Select an address and then start the order
     * @author vpriem
     * @since 20.04.2012
     */
    $(".yd-start-order-from-address").live('click', function(){
        var locationId = this.id.split('-')[2];
        var cityId = this.id.split('-')[3];
        
        if (typeof ydState !== "undefined") {
            ydState.setKind("priv");
            
            if (locationId !== undefined) {
                ydState.setLocation(locationId);
            }
            if (cityId !== undefined) {
                ydState.setCity(cityId);
            }
        }
    });
    
    /**
     * Start order after the address has been selected
     * @author vpriem
     * @since 10.04.2012
     */
    $('#yd-start-order-from-address').live('click', function(){
        var $this = $(this);
        var locationId = $this.data('location');
        var cityId = $this.data('city');
        var href = $this.data('href');
        
        if (typeof ydState !== "undefined") {
            if (locationId !== undefined) {
                ydState.setLocation(locationId);
            }
            if (cityId !== undefined) {
                ydState.setCity(cityId);
            }
        }
        
        if (href !== undefined) {
            this.href = href;
            return true;
        }
        
        return false;
    });
    
    /**
     * Start order
     * @author vpriem
     * @since 16.02.2011
     */
    $('#yd-start-order').live('click', function(){
        $('#yd-start-order-form').submit();
        return false;
    });

    /**
     * Start order from form
     * @author vpriem
     * @since 08.09.2011
     */
    $('#yd-start-order-form').submit(function(){
        if (!this.plz.value.length) {
            notification("error", $.lang("plz-not-given") || $.lang("plzerror"));
            return false;
        }
        
        var cityId = $(this).data('city');
        var href = $(this).data('href');
        
        if (typeof ydState !== "undefined") {
            if (cityId !== undefined) {
                ydState.setCity(cityId);
            }
        }
        
        if (href !== undefined) {
            location.href = href;
            return false;
        }
    });

    /**
     * init the datepicker on the start page
     * @author mlaug
     * @since 05.01.2011
     */
    initDatepicker('now', 'yd-start-deliver-time-day');
    initTimepicker('now', 'yd-start-deliver-time');
    $('#yd-start-deliver-time-day').live('change',function(){
        if(checkDatepickerToday('yd-start-deliver-time-day')){
            setTimepickerNow('yd-start-deliver-time');
            initTimepicker('now', 'yd-start-deliver-time');
        }
        else{
            setTimepickerNow('yd-start-deliver-time');
            initTimepicker('full', 'yd-start-deliver-time');
        }
    });

    /**
     * Take time from given budget times (comp/restaurant)
     * @author vpriem
     * @since 15.02.2011
     */
    if ($('#deliver-time-budget').length) {
        
        if ( btimes.length > 0 ){
            var start = btimes[0].from;
            var end = btimes[btimes.length-1].until;
            initTimepicker('startEnd', 'deliver-time-budget', start, end);
        }
    }
});