$(document).ready(function(){
    
    
      
    /**
     * submit form to select a service from the list
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 16.11.2011
     */
    $('.yd-service-favourites').live('click', function(){
        var $form = $("form", this);
        if (!$form.length) {
            $form = $(this).closest('form');
        }
        
        var service = $(this).parents('div.yd-service-v2')[0].id;
        var id = service.split('-')[2];
        
        var cityId = serviceCitys[id];
        
        ydState.setCity(cityId);
        log("Setting cityId to: " + cityId );
        
        $form.submit();
    });
    
    
    
    
    /**
     * Add favourite
     * @author vpriem 
     * @since 14.02.2010
     * @modified afrank 11.11.11
     */
    $('a.yd-add-fav').live('click', function(){
        var hash = this.id.split('-')[3];
        $.ajax({
            url: '/request_user/addfavourite/id/' + hash,
            dataType: "json",
            success: function (json) {
                if (json.success) {
                    notification("success", json.success);
                    $('#yd-orders-fav-' + hash).removeClass('yd-add-fav');
                    $('#yd-orders-fav-' + hash).addClass('yd-del-fav');
                    $('#yd-orders-fav-' + hash).addClass('inactive');
                    $('#yd-orders-fav-' + hash).prop('title', $.lang('favourites-delete-tooltip'));                  
                    $('#yd-orders-fav-' + hash).simpletooltip();
                    closeDialog();
                }
                else if (json.error) {
                    notification("error", json.error);
                }
            }
        });
        return false;
    });     
    
    
    /**
     * Delete favourite
     * @author Daniel Hahn <hahn@lieferando.de> 
     * @since 18.11.2011
     */
    $('a.yd-del-fav').live('click', function(){
        var hash = this.id.split('-')[3];
        var confirmText = $.lang("favourites-delete-text");
        if(confirm(confirmText)) {
                                          
            //   return false;
            $.ajax({
                url: '/request_user/delfavourite/id/' + hash,                
                dataType: "json",
                success: function (json) {
                    if (json.success) {
                        notification("success", json.success);
                        $('#yd-orders-fav-' + hash).removeClass('yd-del-fav');
                        $('#yd-orders-fav-' + hash).removeClass('inactive');
                        $('#yd-orders-fav-' + hash).addClass('yd-add-fav');
                        $('#yd-orders-fav-' + hash).prop('title', $.lang('favourites-add-tooltip'));                  
                        $('#yd-orders-fav-' + hash).simpletooltip();
                        $('#limit').focus();
                         
                        closeDialog();
                    }
                    else if (json.error) {
                        notification("error", json.error);
                    }
                }
            });
        }
        
        return false;
    });  
       
    /**
     * Delete all favourites for one restaurant
     * use click because template is reused and there are to many events on it
     * @author Daniel Hahn <hahn@lieferando.de> 
     * @since 22.11.2011
     */   
    $('.yd-delete-favourite').click( function(){
        
        var id = this.id.split('-')[3];
        
        if(confirm($.lang('confirm-favorite-delete'))) {
        
            $.ajax({
                url: '/request_user/delfavourite/restId/' + id,                
                dataType: "json",
                success: function (json) {
                    if (json.success) {
                        $('#yd-service-' + id + '-rest').fadeOut();
                        notification("success", json.success);                                                
                        $('#yd-favourite-count').text( $('#yd-favourite-count').text()- 1);
                    }
                    else if (json.error) {
                        notification("error", json.error);
                    }
                }
            });
        }
        
        return false;
    });
       
       
});