$(document).ready(function(){
    if ($.cookie('first_wellcome') !=  '1'){
        openDialog('/request_static/discountfirstaccess', {
           width: '495px',
           modal: true,
           close: function(e, ui) {
               $(ui).dialog('destroy');
           }
       }, function(){
           $.cookie('first_wellcome' ,  '1');
       });
       
    }
    $(".home").scrollTop(0);
})