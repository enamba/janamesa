$(document).ready(function(){
    
    if ($.browser.msie && $.browser.version.split('.')[0] < 9) {
        return;
    }
    
    /**
     * click on enter
     * @author mlaug
     * @since 26.09.2011
     */
    $('#yd-search-service-form').submit(function(event){
        if (!this.search.value.length) {
            log('hitting enter, but not search value so far');
            return false;
        }
        
        log('hitting enter, triggering meal search');
        $('.yd-search-meals').click();
        return false;
    });
    
    /**
     * Search for any meal and append to service
     * @author mlaug
     */
    $('.yd-search-meals').live('click', function(){
        $('.menu-top3').hide();
        _resetSearchList();
        
        var searchValue = $('#yd-search-service').val();
        
        $('#yd-sorting-right-search').hide();
        $('#yd-sorting-right-wait-term').html("<b>" + searchValue + "</b>");
        $('#yd-sorting-right-wait').show();
        
        $.ajax({
            url: '/request_order/search',
            dataType: 'json',
            data: {
                search: searchValue,
                ids: serviceIds
            },
            error: function(){
                _resetSearchList();
            },
            success: function(json){
                if (json.meals.length == 0 && json.services.match.length == 0) {
                    log('no search result found');
                    alert($.lang("no-meals-found"));
                }
                else {
                    var url = '';
                    log('found ' + json.meals.length + ' menus with matching patterns');
                    if (json.meals.length) {
                        $.each(json.meals, function(i, val) {
                            $.each(val, function(j, meal) {
                                url = $('#yd-service-submit-' + meal.restaurantId + '-rest').attr('action');
                                $('#yd-service-menu-' + meal.restaurantId).append(
                                    '<tr>\n\
                                        <td class="first">\n\
                                            <a href="' + url + '?mealid=' + meal.id + '" class="tooltip" title="' + meal.description + '">' + meal.name + '</a>\n\
                                        </td>\n\
                                        <td><a href="' + url + '?mealid=' + meal.id + '" class="tooltip" title="' + meal.description + '">' + meal.cost + '</a></td>\n\
                                    </tr>');
                            });
                        });
                    }
                    
                    log('found ' + json.services.match.length + ' services with matching patterns');
                    if (json.services.match.length) {
                        $.each(json.services.match, function(k, val) {
                            var $s = $('#yd-service-' + val + '-rest');
                            if ($s.length) {
                                $("#yd-filter-found").append($s);
                            }
                        });
                        $.each(json.services.nomatch, function(k, val) {
                            var $s = $('#yd-service-' + val + '-rest');
                            if ($s.length) {
                                $("#yd-filter-the-rest").append($s);
                            }
                        });
                        $('.menu-top3').slideDown('slow');
                    }
                }
                
                $('#yd-sorting-right-wait').hide();
                $('#yd-sorting-right-search').show();
            }
        });
    });

});