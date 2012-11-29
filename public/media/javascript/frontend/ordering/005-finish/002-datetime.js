$(document).ready(function(){

    /**
     * Init datepicker
     * @author vpriem
     * @since 10.02.2011
     */
    if ($('#yd-finish-deliver-time-day').length) {
        initDatepicker('now', 'yd-finish-deliver-time-day');
    }

    /**
     * Load deliver time
     * @author vpriem
     * @since 10.02.2011
     */
    $('#yd-finish-deliver-time-select').bind("refresh", function(){
        var date = $('#yd-finish-deliver-time-day').val();
        var serviceId = $('input:[name="serviceId"]').val();
        var cityId = $('input:[name="cityId"]').val();
        $('#yd-finish-deliver-time-select').load('/request_service/openingtimeday', {
            id: serviceId,
            date: date,
            cityId: cityId,
            mode: ydState.getMode()
        });
    }).trigger("refresh");

    /**
     * Init timepicker
     * @author vpriem
     * @since 10.02.2011
     */
    $('#yd-finish-deliver-time-day').live('change', function(){
        $('#yd-finish-deliver-time-select').trigger("refresh");
    });

});