$(document).ready(function(){
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.06.2012
     */
    
    $("#yd-service-copy-from").bautocomplete('/autocomplete/crm/type/service', function(item){
        if (item.id > 0) {
            $("#yd-service-copy-srcId").val(item.id);
        }
    });

    $("#yd-service-copy-to").bautocomplete('/autocomplete/crm/type/service', function(item){
        if (item.id > 0) {        
            //element
            var restaurant = document.createElement('strong');
            restaurant.appendChild(document.createTextNode(item.label));
            $(restaurant).prop('id', 'yd-discount-restaurant-'+ item.id);

            //a tag for closing
            var restaurantDelete = document.createElement('a');
            restaurantDelete.appendChild(document.createTextNode('x'));
            $(restaurantDelete).addClass('yd-discount-restaurant-delete');
            restaurant.appendChild(restaurantDelete);

            $('#yd-restaurants-list').append(restaurant);

            var restaurantWrapper =  $('#yd-restaurants-list');

            //input tag
            var restaurantInput = document.createElement('input');
            $(restaurantInput).prop('type', 'hidden');
            $(restaurantInput).val(item.id)
            $(restaurantInput).prop('name', 'restaurantIds[]');
            restaurantWrapper.append(restaurantInput);
            restaurantWrapper.parent().show();
            $("#yd-discount-add-restaurant").val('');
            $('#yd-restaurants-list-delete-all').show();
            
            $("#yd-service-copy-to").val('');
        }
    });

});    