$(document).ready(function(){
    
   
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.06.2012
     */
    $("#yd-service-assoc").bautocomplete('/autocomplete/crm/type/service', function(item){
        if (item.id > 0) {
          var span = document.createElement('span');
          
          span.appendChild(document.createTextNode(item.label));
          
          var input = document.createElement('input');
          $(input).prop('name','billingchildren[]');
          $(input).prop('type', 'hidden');
          $(input).val(item.id);
          span.appendChild(input);
          span.appendChild(document.createElement('br'));
          
          $('#yd-service-assoc-add').append(span);
          
          $("#yd-service-assoc").val('');
        }
    });
    
});    