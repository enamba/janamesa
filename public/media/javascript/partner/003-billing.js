$('document').ready(function() {
    
    /* 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.08.2012
     */
    var $form = $('#yd-bill-deliver form');
    
    $form.ajaxForm({
         success: function(resp){
             if (resp.success) {
                  notification('success', resp.success, false, true);
             }
             else if (resp.error) {
                  notification('error', resp.error, false, true);
             }
         },
         dataType: "json"
    });
    
    $(":checkbox", $form).click(function(){
        if ($(this).prop('disabled') != "disabled") {
            $form.submit();
        }
    });
    
    $('.yd-billing-tab').click(function(){
       var tabRef = $(this).attr('data-tab');
       $('.yd-billing-tab').removeClass('active');
       $(this).addClass('active');
       $('.yd-billing-table').hide();
       $('#yd-billing-table-' + tabRef).show()
    });
     
});
