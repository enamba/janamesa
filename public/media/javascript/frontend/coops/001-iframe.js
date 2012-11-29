$(document).ready(function(){
    
    var source_url = decodeURIComponent( document.location.hash.replace( /^#/, '' ) );
    
    $('.yd-frame-submit').each(function(k,href){
       var a = $(href);
       a.attr('href',a.attr('href') + '#' + encodeURIComponent(source_url)); 
    });
    
    /**
     * @author vpriem
     * @since 25.07.2011
     */
    if (!$('.yd-frame').length) {
        return;
    }
       
    /**
     * @author vpriem
     * @since 25.07.2011
     */
    $('#yd-plz-search-iframe')
        .plzAutocomplete(false)
        .focus()
        .caret(0);

    /**
     * @author vpriem
     * @since 25.07.2011
     */
    $("#yd-start-order-form-iframe").submit(function(){
        var cityId = parseInt(this.cityId.value);              
        if (cityId) {
            if (typeof ydState !== "undefined") {
                ydState
                    .setCity(cityId);
            }
            
            location.href = this.action + "/" + cityId + '#' + encodeURIComponent(source_url);
        } else if ($('yd-plz-search-iframe').val().length > 4) {
            location.href = this.action + "/?plz=" + $('yd-plz-search-iframe').val() + '#' + encodeURIComponent(source_url);
        }
        return false;
    });

    /**
     * @author vpriem
     * @since 25.07.2011
     */
    $('#yd-start-order-iframe').live('click', function(){
        $("#yd-start-order-form-iframe").submit();
        return false;
    });

    if ( typeof source_url === 'undefined' ){
    //we should do smth here as well
    }
    else{
        var currentIframeSize = 0; 
        window.setInterval(function() {
            var size = $('body').outerHeight(true);
            if ( currentIframeSize != size ){
                currentIframeSize = size;
                log('sending new size ' + size + ' to parent ' + source_url);
                $.postMessage({if_height : size}, source_url, parent);
            }
        }, 1000);
    }        

    /**
     * @author vpriem
     * @since 12.10.2011
     */
    $('.extras').hide();

    //show special text box for any meal
    $('.show-special').live('click',function(){
        var id = $(this).attr('id').split('-')[2];
        $('#special-'+id).slideToggle('fast');
    });

    /**
     * @author vpriem
     * @since 12.10.2011
     */
    $(".click-no-size").click(function(){
       $(this).closest("tr")
              .find("td.right:first")
              .click(); 
    });

    /**
     * @author vpriem
     * @since 12.10.2011
     */
    $('.click').click(function(){
        var mealId = this.id.split('-')[2];
        var sizeId = this.id.split('-')[3];
        
        if ($(this).hasClass('activeiframe')) {
            $('.activeiframe').removeClass('activeiframe');
            $('.extras').hide();
            return;
        }
        
        $('.activeiframe').removeClass('activeiframe');
        $('.extras').hide();
        $(this).addClass('activeiframe');

        // deselect all extras and options
        $('.addExtra').attr('checked', false);
        $('.yd-option-row-checkbox').attr('checked', false);

        $('#yd-extras-' + mealId + '-' + sizeId).show();
        if (!$('#yd-extras-' + mealId + '-' + sizeId).hasClass("loaded")) {
            $.ajax({
                url: '/request_iframe/callmeal/',
                data: {
                    id: mealId,
                    size: sizeId
                },
                success: function (html) {
                    $('#yd-extras-' + mealId + '-' + sizeId)
                        .addClass("loaded")
                        .html(html);
                }
            });
        }
    }); 

    // deprecated ?
    $('.clickd').live('click', function(){
        $('.click').removeClass('activeiframe');
        if ($(this).hasClass('right')) {
            $(this).addClass('activeiframe');
        }
        else{
            $(this).parent().find('.click-size:first').addClass('activeiframe');
        }
    });

    $(".food_side-iframe").live("click", function() {
        var catId = this.id.split('-')[1];
        $('.active_cat').hide();
        $('.active').removeClass('active');
        $('#yd-category-'+catId).show().addClass('active_cat');
        $(this).addClass('active');
        return false;
    });

    $('.yd-frame-payment').live('click', function(){
        if ( ydOrder.is_minamount_reached() ){            
            log('submitting form to parent');
            $('#yd-finish-order-form').submit();
            return false;
        }
        else{
            log('min amount has not been reached');               
            return false;
        }
    });

    $('.yd-select-service-and-switch-to-parent').live('click', function(){
        window.top.location.href = $(this).attr('href');
    });

    $('.yd-select-service-and-stay-in-frame').live('click', function(){
        location.href = $(this).attr('href');
    });

});