
function createLocation(){
    $('#dialog-locations').dialog({
        width: 600,
        height: 380,
        modal: true,
        close: function(e, ui) {
            $(ui).dialog('destroy');
        },
        open: function() {
            $('.yd-new-address-form').find("input, textarea").each(function() {
                $(this).removeClass('yd-form-invalid');
            });
            $('.formError').hide();
        }
    });
}

$(document).ready(function(){
    
    $('a.yd-form-toggle').live('click',function(){      
        var $this = $(this);
        var $form = $this.closest('form');
        $form.removeClass('warning'); 
        $this.hide();
        $this.next().show();
        
    });
    
    /**
     * Submit for Location Form
     * @author Daniel Hahn <hahn@lieferando.de>
     *
     */
    $('a.yd-edit-location, a.yd-create-location').live('click',function(){
        var $this = $(this);
        var $form = $this.closest('form');
        
        log('submitting location form');
        
        $form.ajaxSubmit({
            dataType: "html",
            beforeSubmit: function(){ 
                $this.hide();  
                $this.prev().prev().show();
            },
            success : function(responseText){
                if ( responseText.length ){
                    
                    //append created location
                    $('.yd-profile-table')
                    .append($(responseText).formToggle());                    
                    $('#dialog-locations').dialog('close');
                    $($form).find("input, textarea").each(function(i ,elem) {
                        $(elem).removeClass('yd-form-invalid')
                        .removeClass('ui-autocomplete-loading');
                        elem.value = '';
                    });
                    ydCustomer.setHasLocations(1);
                }
                else{
                    $form.removeClass('warning').formToggle();
                   
                }
                $this.prev().show().prev().hide();
                $('.yd-create-location').show();
            },
            error : function(data){
                               
                var json = $.parseJSON(data.responseText);
                           
                var elements = Array();
                
                if($form.hasClass('yd-new-address-form')) {
                    $('.formError').hide();
                    $($form).find("input, textarea").each(function(i ,elem) {
                        $(elem).removeClass('yd-form-invalid');
                    });
                    $.each(json, function(i, elem) {                                          
                        elements.push($($form).find("input[name='"+i +"']" +", textarea[name='" + i + "']"));                  
                        $.each(elements, function(j, item) {                                                      
                            if($(item).prop('name') == i && !$(item).is(':hidden') && $(item).prop('name') != 'plz') {
                                $(item).addClass('yd-form-invalid'); 
                                $(item).next().children().first().text(elem);
                                $(item).next().show();
                            }
                        
                        });
                        
                        if(i == 'cityId') {
                            var plzElem = $($form).find("input[name='plz']");                           
                            $(plzElem).addClass('yd-form-invalid'); 
                            $(plzElem).next().children().first().text(elem);
                            $(plzElem).next().show();
                        }
                        
                    });
                }
                $form.addClass('warning');
                
                //log(elements);
                $('.yd-create-location').show();                
                $form.find('.td-check').show();
                $form.find('.td-edit').hide();
                $form.find('.yd-please-wait').hide();
            },
            clearForm : $this.hasClass('yd-create-location')
        });
    });
    
    
    $(".formError").live("click",function(){	 // REMOVE BOX ON CLICK
        $(".formError").fadeOut(150,function(){
            $(".formError").hide();
        });
    });
    
    /**
     *  popup für location create
     *  @author Daniel Hahn <hahn@lieferando.de>
     *  @since 24.11.2011
     */
    $('#yd-user-location-create').live('click',function() {
        createLocation();
    });
    
     
    /**
     * mark a location as primary address
     * @author mlaug
     * @since 10.11.2011
     */
    $('a.yd-heart-location').live('click', function(){
        var id = this.id.split('-')[3];
        var $this = $(this);
        var toggle = $this.hasClass('active') ? 0 : 1;
        $('.yd-heart-location').not($this).removeClass('active');
        $this.toggleClass('active');
        $.ajax({
            url: '/request_user_location/primary/id/' + id + '/toggle/' + toggle,
            error : function(){
            //mh
            }
        });
    });

    /**
     * Add company address
     * @author vpriem
     * @since 09.02.2011
     */
    $('a.yd-add-caddress').live('click', function(){
        location.href = '/company/address';
        return false;
    });

    /**
     * Edit company address
     * @author vpriem
     * @since 09.02.2011
     */
    $('.yd-link-edit-caddress').live('click', function(){
        var id = this.id.split('-')[1];
        location.href = "/company/address/id/" + id;
        return false;
    });
    
    /**
     * hightlight based on dash
     * @author mlaug
     * @since 17.11.2011
     */
    if ( document.location.hash && $('.yd-profile').length > 0){
        $(document.location.hash).closest('form').find('.td').css('background-color','#ffc');
    }

    /**
     * automatically open "new adress" lightbox
     *
     * @author mlaug
     * @since 01.12.2011
     */
    if ( document.location.hash && document.location.hash == '#new_address' && $('.yd-profile').length > 0){
        createLocation();
    }

});
