$(document).ready(function(){
    
    /**
     * check if any service is not reachable and remove it from the cached list
     * @author mlaug
     * @since 28.09.2011
     */
    if (!$('.yd-service-page').length) {
        return;
    }
    
    if (ydState.maybeLoggedIn()) {        
        $('.yd-sorting-type').show();
    }
    
    $.ajax({
        type: 'POST',
        url: '/request_service/reachable',
        data: {
            kind: ydState.getKind(),
            ids: serviceIds, // defined in service.htm
            cityId: ydState.getCity()
        },
        dataType: "json",
        success: function(json){

            if (typeof json == 'undefined') {
                return;
            }
            
            if (json.permission.employee && ydState.getKind() == 'comp') {
                var checkRest = $('#yd-filter-service-type-rest');
                var checkCater = $('#yd-filter-service-type-cater');
                var checkGreat = $('#yd-filter-service-type-great');

                var permRestInfo = $('#yd-disallow-company-rest');
                var permCaterInfo = $('#yd-disallow-company-cater');
                var permGreatInfo = $('#yd-disallow-company-great');

                if (json.permission.budget <= 0) {
                    checkRest.attr('disabled', true)
                             .attr('checked', false);
                    permRestInfo.show();
                }
                
                if (!json.permission.cater) {
                    checkCater.attr('disabled', true)
                              .attr('checked', false);
                    permCaterInfo.show();
                }
                
                if (!json.permission.great) {
                    checkGreat.attr('disabled', true)
                               .attr('checked', false);
                    permGreatInfo.show();
                }
                
                initServiceTypeFilter();
            }
            
            $.each(json.notReachable, function(k, id) {
                log('removing ' + id + ' from list');
                // this will remove the service
                $('#yd-service-' + id + '-rest, #yd-service-' + id + '-cater, #yd-service-' + id + '-great, #yd-service-' + id + '-fruit').remove();
                // this will remove the proposals from the sidebar
                $('#yd-service-' + id + '-rating').remove();
            });

            $.each(json.notAvailable, function(k, id) {
                log('moving ' + id + ' from list');
                // this will move the service and set the offline status
                var $s = $('#yd-service-' + id + '-rest, #yd-service-' + id + '-cater, #yd-service-' + id + '-great, #yd-service-' + id + '-fruit')
                    .prependTo("#yd-filter-offline");
                // tricky but works
                $s.find("form")
                    .addClass("service-offline");
                $s.find(".yd-sv3-4, .yd-sv3-5, .yd-sv3-61, .yd-sv3-62, .yd-sv3-63, .yd-sv3-64")
                    .hide();
                $s.find(".yd-sv3-4:first")
                    .show(); 
            });

            trackServices();
        }
    }); 
});