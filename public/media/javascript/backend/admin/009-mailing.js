
$(document).ready(function(){

    $("#yd-mailing-parameters").multiselect({
        noneSelectedText: "Parameter auswählen",
        selectedText: '# ausgewählt',
        
    });


    function renderItem(item) {
        var city = document.createElement('strong');
        city.appendChild(document.createTextNode(item.label));
        $(city).prop('id', 'yd-mailing-city-'+item.id);
        
        //a tag for closing
        var cityDelete = document.createElement('a');
        cityDelete.appendChild(document.createTextNode('x'));
        $(cityDelete).addClass('yd-mailing-city-delete');
        city.appendChild(cityDelete);
        
        $('#yd-mailing-citys').append(city);
        
        //input tag
        var cityInput = document.createElement('input');
        $(cityInput).prop('type', 'hidden');
        $(cityInput).val(item.id)
        $(cityInput).prop('name', 'cityIds[]');
        var cityWrapper =  $('#yd-mailing-citys');
        cityWrapper.append(cityInput);
        cityWrapper.parent().show();
        $("#yd-mailing-add-city").val('');
        $('#yd-mailing-citys-delete-all').show();
    }

    $("#yd-mailing-add-city").cautocomplete('/autocomplete/cityplz', 
        function(item){
            //span
            log('hier');
            renderItem(item);
        },
        
        function(event) {            
            $('ul.ui-autocomplete').prepend('<li ><a class="ui-corner-all" tabindex="-1" id="yd-discount-add-city-multiple">'+ event.target.value + '...</a></li>');
            $('ul.ui-autocomplete').on('click','#yd-discount-add-city-multiple', function(){                
                
                $('ul.ui-autocomplete li.ui-menu-item').filter(':visible').each(function(i, item){
                    var data = $(item).data('item.autocomplete');
                   
                    if(data !== undefined) {
                        log(data.id)
                        renderItem(data);
                    }
                });                                              
                $('ul.ui-autocomplete li').hide();                                   
            });            
            return false;
        });
        
        
    $('#yd-mailing-citys').on('click', '.yd-mailing-city-delete', function(event) {
        var span = $(this).parent('strong');    
        $(span[0]).next().remove();
        $(span[0]).remove();       
        if($('#yd-mailing-citys').children().length == 0) {
            $('#yd-mailing-citys-delete-all').hide();
            $('#yd-mailing-citys').parent().hide();
        }         
    })

    $('#yd-mailing-citys-delete-all').click(function(){
        var cityWrapper =  $('#yd-mailing-citys');
        cityWrapper.empty();
        $('#yd-mailing-citys-delete-all').hide();
        cityWrapper.parent().hide();
    });
    
});