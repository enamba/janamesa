$(document).ready(function(){
    if ($.cookie('first_wellcome') !=  '1'){
        openDialog('/request_static/discountfirstaccess', {
           width: '495px',
           modal: true,
           close: function(e, ui) {
               $(ui).dialog('destroy');
           }
       }, function(){             
           $("#yd-yes-i-want").click(function() {
               $.cookie('yd-preorder', $.base64.encode(id));
               window.location.href = $form.attr("action");
               return false;
           });
       });
       $.cookie('first_wellcome' ,  '1');
    }
})