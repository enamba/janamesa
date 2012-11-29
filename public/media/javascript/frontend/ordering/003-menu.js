/**
 * switch through different categories of service menu
 * @author mlaug
 * @since 05.01.2011
 * @view /order/_includes/menu/menu.htm
 * @view /satellite/_menu.htm
 */
function toggleMenuCategory(cat){
    if ( cat === undefined || cat === null ){
        log('cat is undefined or null');
        return false;   
    }
    
    var $cat = $(cat);
    var data = cat.id.split('-');
    var catId = data[1];
    var parentCatId = data[3];
    
    $('.food_side').removeClass('active');
    $('.menu-box').hide();
    
    //if we did not click one of the childs
    if ( !$cat.hasClass('yd-category-children') ){    
        log('did not click a chlid, hide all of them');
        $('.yd-menu-nav2').hide();
        $('.yd-category-children').hide();
    }
    
    $cat.addClass('active');
    
    //toggle just one child
    if ( parentCatId <= 0 ){      
        if ( data.length == 6 ){
            log('marking parent as active');
            parentCatId = data[5];
            $('#cat-0-side-' + parentCatId).addClass('active');
        }
        $('#yd-category-'+catId).show();
        log('toggling category ' + catId);
    }
    //toggle all parents
    else{
        $('.yd-category-'+parentCatId).show();
        $('.yd-category-children-'+parentCatId).show();
        $('.yd-menu-nav2').slideDown('fast');
        log('toggling childs of parent category ' + parentCatId);     
    }
}
 
/**
 * service tabs 
 * @author oknoblich
 * @since 05.10.2011
 */   
function tabmenu(tab){
    for(var i=1;i<=3;i++){
        if(i==tab){
            $('#yd-menu-toggle-' + i).removeClass('hidden');
            $('.tab' + i).addClass('active');
        }
        else {
            $('#yd-menu-toggle-' + i).addClass('hidden');
            $('.tab' + i).removeClass('active');
        }
    }
}

$(document).ready(function(){
    
    /**
     * new div based menu 2011
     * @author oknoblich
     * @since 10.10.2011
     */

    // CSS helper
    $('.yd-menu-modern .table .tr .td div:first-child').after('<hr style="height:10px" />');
    $('.yd-menu-modern .table .tr .td div:first-child').Zoomer();

    // IE 6 & 7 helper
    $('.ie6 .yd-menu-modern .table .tr .td hr').hide();
    $('.ie7 .yd-menu-modern .table .tr .td hr').hide();
    $('.ie6 .yd-menu-modern .table .tr .td:first-child').addClass('first');
    $('.ie7 .yd-menu-modern .table .tr .td:first-child').addClass('first');
    $('.ie6 .yd-menu-modern .table .thead .th:first-child').addClass('first');
    $('.ie7 .yd-menu-modern .table .thead .th:first-child').addClass('first');

    // Avanti helper
    $('.yd-menu-modern .th:nth-child(1), .yd-menu-modern .td:nth-child(1)').addClass('nth-child-1');
    $('.yd-menu-modern .th:nth-child(2), .yd-menu-modern .td:nth-child(2)').addClass('nth-child-2');
    $('.yd-menu-modern .th:nth-child(3), .yd-menu-modern .td:nth-child(3)').addClass('nth-child-3');
    $('.yd-menu-modern .th:nth-child(4), .yd-menu-modern .td:nth-child(4)').addClass('nth-child-4');
    $('.yd-menu-modern .th:nth-child(5), .yd-menu-modern .td:nth-child(5)').addClass('nth-child-5');

    $(".yd-menu-timcat img").error(function () { 
        $(this).hide(); 
    });

    /**
     * Toggle first category or the main one
     * @author vpriem
     * @since 08.06.2012
     */
    $(".yd-menu-nav1").each(function(){
        var $mainCat = $('li a.category-main', this);
        toggleMenuCategory($mainCat.length ? $mainCat[0] : $('li a', this)[0]);
    });
    
    /**
     * Restore order if cart is present
     * @author vpriem
     * @since 28.07.2011
     */
 
    $('input[name="restore"]').each(function(){
        var serviceId = $('input:[name="serviceId"]').val();
        if ( serviceId ){
            log('found serviceId, will restore ydOrder object');
            //we check for the "yd-no-update" class, which would
            //indicate, that we do not want to update the view
            ydOrder.set_service(serviceId)
            .restore(!$(this).hasClass('yd-no-update'));
            
        }
        return false;
    });
    
    $('a.yd-menu-toggle-1a, li.yd-menu-toggle-1a').live('click',function(){
        tabmenu(1);
        $('.tab4').removeClass('hidden');
        return false;
    });
    
    $('a.yd-menu-toggle-2a, li.yd-menu-toggle-2a, .yd-menu-detail-rate').live('click',function(){
        tabmenu(2);
        $('.tab4').addClass('hidden');
        return false;
    });
    
    $('a.yd-menu-toggle-3a, li.yd-menu-toggle-3a').live('click',function(){
        tabmenu(3);
        $('.tab4').addClass('hidden');
        return false;
    });
    
    /**
     * show / hide full category name in menu on mouseover / -out
     * @todo: this can be done with toggle!
     * @author oknoblich
     * @since 10.10.2011
     */
    $('.category-lenght-toggle').live('mouseover', function(){
        $(this).children('.category-lenght-toggle-full').show();
    });
    $('.category-lenght-toggle').live('mouseout', function(){
        $(this).children('.category-lenght-toggle-full').hide();
    });

    /**
     * menu category switch
     * @author mlaug
     * @since 05.01.2011
     */
    
    $(".food_side").live("click", function() {
        toggleMenuCategory(this);
        return false;     
    });


    /**
     * increase count of an item of card
     * @author mlaug
     * @since 07.01.2011
     */
    $(".increase-item").live("click", function () {
        var hash = this.id.split('-')[3];
        ydOrder.increase_meal(hash);
    });
    
    /**
     * change count of an item of card
     * @author vpriem
     * @since 13.07.2011
     */
    $(".yd-change-item-count").live("keyup", function(){
        if (this.value.length) {
            var hash = this.id.split('-')[3];
            ydOrder.set_count(hash, this.value);
        }
    });

    /**
     * decrese count of an item from standard cart
     * @author mlaug
     * @since 07.01.2011
     * @todo alter naming, its confusing
     */
    $(".decrease-item").live("click", function () {
        var hash = this.id.split('-')[3];
        ydOrder.decrease_meal(hash);
    });

    /**
     * delete an item from standard cart
     * @author mlaug
     * @since 07.01.2011
     */
    $(".delete-item").live('click', function () {
        var hash = this.id.split('-')[3];
        ydOrder.remove_meal(hash);
    });
    
    /**
     * Submit finish form
     * even if minamount is reached
     * @author vpriem
     * @since 20.07.2011
     */
    $(".yd-finish-order").live('click', function(){
        if ($(this).hasClass('yd-gray-button')) {
            log('it seems like, the minamount hasn\'t been reached');
            return false;
        }
        
        $('#yd-finish-order-form').submit();
        return false;
    });
    
    /**
     * Switch finish form action
     * @author vpriem
     * @since 20.07.2011
     */
    $('#yd-finish-order-form').each(function(){

        // pretty hack for satellite
        // we need to take the attr here, since every browser would
        // return something else with this.action
        if ($(this).attr('action').length) {
            return;
        }
        
        if (typeof ydState !== "undefined") {
            if (ydState.getKind() == 'comp') {
                log('setting form to company finish page');
                this.action = '/order_company/finish';
            }
            else{
                log('setting form to private finish page');
                this.action = '/order_private/finish';
            }
        }
    });
    
    /**
     * new lightbox special comment
     * @author oknoblich
     * @since 07.11.2011
     */

    $(".yd-dialogs-comment-head").live("click", function(){
        $(".yd-dialogs-comment-body").slideToggle('fast');
    });
    
    /**
     * open a lightbox for meal editing
     * @author alex
     * @since 02.12.2011
     */
    $(".yd-shopping-article").live('click', function () {
        var hash = this.id.split('-')[2];
        updateMeal(hash);
    });
    
});
