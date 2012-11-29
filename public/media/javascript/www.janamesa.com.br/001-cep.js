function cityVerboseAutocomplete(){
    log('using cep autocomplete');
    var process = $(this).hasClass('br-cep-location') ? 'location' : 'order';
    openDialog('/request_cityverbose_autocomplete/cep?process=' + process,{
        width: 600,
        height: 380,
        modal: true,
        close: function(e, ui) {
            $(ui).dialog('destroy');
        }
    }, function (){
        $('#ym-start-order').live('click',function(){
            var $this = $(this);
            var text = $this.val();
            $this.val('... working ...');
            $('#br-results-loading').show();
            $.ajax({
                url :  '/request_cityverbose_autocomplete/list',
                type : 'post',
                data : {
                    'process' : $('input[name="process"]').val(),
                    'cidade' : $('#br-cidade').val(),
                    'logradouro' : $('#br-logradouro').val()
                },
                success : function(html){
                    $this.val(text);
                    $('#br-results-loading').hide();
                    $('.br-results').html(html);
                }
            });
            return false; 
        });
    });
    return false;
} 

$(document).ready(function(){
    
    //destroy autocomplete
    $('#yd-plz-search').autocomplete('destroy');
    $('#plz').autocomplete('destroy');
    $('.yd-plz-autocomplete').plzAutocomplete();
    
    //if the customer wants to search for this cep, use this lightbox
    $('#br-cep-autocomplete, #br-cep-autocomplete2, #yd-start-order').live('click', cityVerboseAutocomplete);

    //autocomplete process select city id to add addresses
    $('.jm-select-cep').live('click',function(){
        var cityId = $(this).attr('data-id');
        var cep = $(this).attr('data-cep');
        var street = $(this).attr('data-street');
        $('input[name="street"]').val(street);
        $('input[name="cityId"]').val(cityId);
        $('input[name="plz"]').val(cep);
        closeDialog();
    });
    
    $('#yd-plz-search').bind('keypress', function(e){
        if(e.keyCode==13){
            document.cep_form.submit();
            e.preventDefault();
        }
    });
    
});