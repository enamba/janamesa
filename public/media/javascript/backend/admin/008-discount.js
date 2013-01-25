/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    
    // datepicker for discount creation start time
    if ( $('#yd-discount-create-start').length > 0){
        initDatepicker('full', 'yd-discount-create-start');
    }

    // datepicker for discount creation end time
    if ( $('#yd-discount-create-end').length > 0){
        initDatepicker('full', 'yd-discount-create-end');
    }
    
    // check change
    $('#yd-admin-discount-create-usage').live('change', function(){
        if ($(this).val() == 2) {
            $('#yd-admin-discount-create-usage-count').show();
        } else {
            $('#yd-admin-discount-create-usage-count').hide();
        }
    }).trigger("change");
      
    
    //show information about discount action type
    $('#yd-rabatt_type').live('change', function(){
        $('#yd-admin-discount-create-usage-count').hide();

        var val = $(this).val();
        if (val == "0") {
            $('#yd-rabatt_type_info').html('Neukunden & Bestandskunden, einfache Rabattaktion mit Code, keine Verifikation, keine Landingpage ');    
            $('#yd-admin-discount-fakeCode').val('');
            $('#yd-admin-discount-create-usage-row').show();
            $('#yd-admin-discount-create-usage option[value="0"]').prop('disabled',false);
            $('#yd-admin-discount-referer-row').hide();
            $('#yd-admin-discount-referer').val('');
            $('#yd-admin-discount-img-row').hide();
            $('#yd-admin-discount-number').val('1');
            $('#yd-admin-discount-newCustomerCheck').hide();
            $('#yd-admin-discount-restaurants').hide();
            $('#yd-admin-discount-citys').hide();
        }
        else {
            $('#yd-admin-discount-create-usage-row').hide();
            $('#yd-admin-discount-create-usage').val('0');
            $('#yd-admin-discount-referer-row').show();
            $('#yd-admin-discount-img-row').show();
            $('#yd-admin-discount-number').val('');
            
            if (val == "1") {
                $('#yd-rabatt_type_info').html('Neukunden, ohne Code, mit Verifikation, mit Landingpage');
                $('#yd-admin-discount-newCustomerCheck').show();
                $('#yd-admin-discount-restaurants').hide();
                $('#yd-admin-discount-citys').hide();
            }
            else if (val == "2") {
                $('#yd-rabatt_type_info').html('Neukunden, mit Code, mit Verifikation, mit Landingpage ');
                $('#yd-admin-discount-newCustomerCheck').show();
                $('#yd-admin-discount-restaurants').hide();
                $('#yd-admin-discount-citys').hide();
            }
            else if (val == "3") {
                $('#yd-rabatt_type_info').html('Neukunden, mit universellem Code, mit Verifikation, mit Landingpage');
                $('#yd-admin-discount-newCustomerCheck').show();
                $('#yd-admin-discount-restaurants').hide();
                $('#yd-admin-discount-citys').hide();
            }
            else if (val == "4") {
                $('#yd-rabatt_type_info').html('Neu- und Bestandskunden, einmaliger Code, ohne Verifikation, ohne Landingpage, nur 1 Gutschein pro Kunde pro Aktion');
                $('#yd-admin-discount-referer-row').hide();
                $('#yd-admin-discount-img-row').hide();                
                $('#yd-admin-discount-newCustomerCheck').hide();
                $('#yd-admin-discount-restaurants').show();
                $('#yd-admin-discount-citys').show();
            }
            else if (val == "5") {
                $('#yd-rabatt_type_info').html('Neukunden, einmaliger Code, ohne Verifikation, ohne Landingpage, nur 1 Gutschein pro Kunde');
                $('#yd-admin-discount-referer-row').hide();
                $('#yd-admin-discount-img-row').hide();                
                $('#yd-admin-discount-newCustomerCheck').hide();
                $('#yd-admin-discount-restaurants').show();
                $('#yd-admin-discount-citys').show();
            } else if (val == "6") {
                $('#yd-rabatt_type_info').html('Neu- und Bestandskunden, universeller Code, ohne Verifikation, ohne Landingpage, nur 1 Gutschein pro Kunde pro Aktion');
                $('#yd-admin-discount-referer-row').hide();
                $('#yd-admin-discount-img-row').hide();         
                $('#yd-admin-discount-newCustomerCheck').hide();
                $('#yd-admin-discount-restaurants').show();
                $('#yd-admin-discount-citys').show();
            } else if (val == "7") {
                $('#yd-rabatt_type_info').html('Neukunden, universeller Code, ohne Verifikation, ohne Landingpage');
                $('#yd-admin-discount-referer-row').hide();
                $('#yd-admin-discount-img-row').hide();         
                $('#yd-admin-discount-create-usage-row').show();
                $('#yd-admin-discount-create-usage').val('1');
               $('#yd-admin-discount-create-usage option[value="0"]').prop('disabled',true);
                $('#yd-admin-discount-newCustomerCheck').hide();
                $('#yd-admin-discount-restaurants').show();
                $('#yd-admin-discount-citys').show();
            }
        }
                
        if (val == "1") {
            $('#yd-admin-discount-code-count-row').hide();
            $('#yd-admin-discount-fake-code-row').hide();
        }
        else if (val == "3" || val == "6" || val == '7') {
            $('#yd-admin-discount-code-count-row').hide();
            $('#yd-admin-discount-fake-code-row').show();                        
        }
        else {
            $('#yd-admin-discount-code-count-row').show();
            $('#yd-admin-discount-fake-code-row').hide();
        }      
    }).trigger("change");

    //restaurant restriction

    $('#yd-discount-restaurants-restriction').click(function() {
        if($(this).prop('checked')) {
            $('#yd-discount-add-restaurant').show();      
            $('#yd-discount-add-city').hide();
            $('#yd-discount-citys-restriction').prop('checked', false);
            $('#yd-discount-citys').empty();
            $('#yd-discount-citys-delete-all').hide();
        }else {
            $('#yd-discount-add-restaurant').hide();
        }          
    });

    $("#yd-discount-add-restaurant").bautocomplete('/autocomplete/crm/type/service', function(item) {
        
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
    });

    $('#yd-restaurants-list').on('click', '.yd-discount-restaurant-delete', function(event) {
        var span = $(this).parent('strong');    
        $(span[0]).next().remove();
        $(span[0]).remove();
        if($('#yd-restaurants-list').children().length == 0) {
            $('#yd-restaurants-list-delete-all').hide();
            $('#yd-restaurants-list').parent().hide();
        }               
    })
    
    $('#yd-restaurants-list-delete-all').click(function(){
        $('#yd-restaurants-list').empty();
        $('#yd-restaurants-list-delete-all').hide();
        $('#yd-restaurants-list').parent().hide();
    })
    
    //city restriction
  
    $('#yd-discount-citys-restriction').click(function() {
        if($(this).prop('checked')) {
            $('#yd-discount-add-city').show();           
            $('#yd-discount-add-restaurant').hide();
            $('#yd-discount-restaurants-restriction').prop('checked', false);
            $('#yd-restaurants-list').empty();
            $('#yd-restaurants-list-delete-all').hide();
        }else {
            $('#yd-discount-add-city').hide();
        }
    });

    function renderItem(item) {
        var city = document.createElement('strong');
        city.appendChild(document.createTextNode(item.label));
        $(city).prop('id', 'yd-discount-city-'+item.id);
        
        //a tag for closing
        var cityDelete = document.createElement('a');
        cityDelete.appendChild(document.createTextNode('x'));
        $(cityDelete).addClass('yd-discount-city-delete');
        city.appendChild(cityDelete);
        
        $('#yd-discount-citys').append(city);
        
        //input tag
        var cityInput = document.createElement('input');
        $(cityInput).prop('type', 'hidden');
        $(cityInput).val(item.id)
        $(cityInput).prop('name', 'cityIds[]');
        var cityWrapper =  $('#yd-discount-citys');
        cityWrapper.append(cityInput);
        cityWrapper.parent().show();
        $("#yd-discount-add-city").val('');
        $('#yd-discount-citys-delete-all').show();
    }

//    $("#yd-discount-add-city").cautocomplete('/autocomplete/cityplz', 
//        function(item){
//            //span
//            log('hier');
//            renderItem(item);
//        },
//        
//        function(event) {            
//            $('ul.ui-autocomplete').prepend('<li ><a class="ui-corner-all" tabindex="-1" id="yd-discount-add-city-multiple">'+ event.target.value + '...</a></li>');
//            $('ul.ui-autocomplete').on('click','#yd-discount-add-city-multiple', function(){                
//                
//                $('ul.ui-autocomplete li.ui-menu-item').filter(':visible').each(function(i, item){
//                    var data = $(item).data('item.autocomplete');
//                   
//                    if(data !== undefined) {
//                        log(data.id)
//                        renderItem(data);
//                    }
//                });                                              
//                $('ul.ui-autocomplete li').hide();                                   
//            });            
//            return false;
//        });
//        
//        
    $('#yd-discount-citys').on('click', '.yd-discount-city-delete', function(event) {
        var span = $(this).parent('strong');    
        $(span[0]).next().remove();
        $(span[0]).remove();       
        if($('#yd-discount-citys').children().length == 0) {
            $('#yd-discount-citys-delete-all').hide();
            $('#yd-discount-citys').parent().hide();
        }         
    })

    $('#yd-discount-citys-delete-all').click(function(){
        var cityWrapper =  $('#yd-discount-citys');
        cityWrapper.empty();
        $('#yd-discount-citys-delete-all').hide();
        cityWrapper.parent().hide();
    })

});