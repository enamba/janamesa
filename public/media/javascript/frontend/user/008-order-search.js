$(document).ready(function(){
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de> 
     * @since 16.11.2011
     * @link /user/orders.htm
     */
    var searchInput = $('#yd-order-search');
    
    if(typeof searchInput == "undefined") {
        return;
    }
    
    
   searchInput.live('keypress focusout', function() {
             
         var search = $('#yd-order-search').val();
         
         if(search.length < 3) {
             
           $('tr').each(function(k, val ){               
                $(val).show();                                             
            });             
             return;
         }      
        var foundRows = [];
    
        $('td.searchable').each(function(k,val){
            var text = $(val).text();        
        
            if(text.toLowerCase().indexOf(search.toLowerCase()) != -1) {
               
                foundRows.push($(val).parent('tr'));
            }                                  
        });
        $('tr').each(function(k, val ){
            if(k > 1) {
                 $(val).hide();        
            }                                                
        });   
        $(foundRows).each(function(k, val ){        
            $(val).show();                                             
        });           
    });
});

