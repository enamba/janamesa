
function discountInfo($box, $container) {

    var rabattCodeId = $(this).attr('data-rabattCodeId');
    var orderId = $(this).attr('data-orderId');
    log('getting discoutn infobox for ' + rabattCodeId + '/' );
            
    $.ajax({
        cache: true,
        url: '/administration_request_grid_discount/infobox',
        data: {
            rabattCodeId: rabattCodeId,
            orderId: orderId
        },
        success: function(html){
            $container.html(html);
            $box.show();
        }
    });
}


$(document).ready(function(){
    
    //show popup with discount code information and reset option
    $('.yd-edit-rabattcode').live('click', function(){
        var id = this.id.split('-')[3];
        var orderId = this.id.split('-')[4];
        if (id) {
            
            openDialog('/request_administration/rabattcodeedit/id/'+id+'/order/'+orderId,  {
                width: 600,
                height: 380,
                modal: true,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                }
                
            },  function() {
                $('.yd-grid-trigger', this).gridBox();                   
            }
            );
        }
        $('.yd-grid-box').hide();       
        this.blur();
        return false;
    });
    
    
    //reset the discount
    $('.yd-rabattcode-reset').live('click',function(){
        var id = $(this).attr('id').split('-')[3];
        var orderId = $(this).attr('id').split('-')[4];
        
        $.ajax({
            url: '/request_administration/rabattcodeedit/id/' + id + '/command/reset/order/'+orderId,
            success: function(html){
                $('.be-dialogs').replaceWith(html);
                $('.yd-grid-trigger', $('.be-dialogs')).gridBox();                   
            },
            dataType: "html"
        }); 
        
    });

    
    
});