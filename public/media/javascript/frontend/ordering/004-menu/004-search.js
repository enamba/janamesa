$(document).ready(function(){

    if ( $.browser.msie && $.browser.version.split('.')[0] < 9 ){
        return;
    }
    
    var defaultSearchVal = $('.menu-nav-search-text').val();
    
    /**
     * check if any search action is needed
     * @author mlaug
     * @since 12.10.2011
     */
    function checkForAction(){
        var currentVal = $('.menu-nav-search-text').val();
        if ( currentVal != undefined && currentVal != defaultSearchVal && currentVal.length > 0 ){
            $('.tab4 .yd-search-reset').show();
            return true;          
        }
        
        if ( currentVal.length == 0 ){
            resetMenuSearch(false);
        }
        
        $('.tab4 .yd-search-reset').hide();
        return false;
    }
    
    /**
     * reset the search in the menu
     * @author mlaug
     * @since 12.10.2011
     */
    function resetMenuSearch(setDefaultValue){
        if ( setDefaultValue ){
            $('#menu-nav-search-text').val(defaultSearchVal); 
        }
        $('.yd-search-reset').hide();
        $('.search-no-meal-in-cat').hide();
        $('.yd-menu-search').show();
        $('.food_side').each(function(){
            if ( this.id.split('-')[1] != 0 ){             
                toggleMenuCategory(this);
                return false;
            }
        });
    }

    /**
     * @author mlaug
     * @since 05.01.2010
     */
    $('.menu-nav-search-text').live('click, focus', function(){
        var val = this.value;
        if (val == defaultSearchVal) {
            $(this)
            .val("")
            .css('color','black');
        }
    });

    /**
     * @author mlaug
     * @since 05.01.2010
     */
    $('.menu-nav-search-text').live('blur',function(){
        if (this.value == '') {
            $(this)
            .val(defaultSearchVal)
            .css('color','#AAA');
        }
    });

    /**
     * @author mlaug
     * @since 05.01.2010
     */
    $('.yd-search-reset').live('click', function(){
        resetMenuSearch(true);
    });

    /**
     * search in the menu
     * @view /application/views/smarty/template/default/order/_includes/menu/menu.htm
     * @author mlaug
     * @since 06.10.2011
     */
    if ($('.menu-nav-search-text').length) {       
        
        $('.menu-nav-search-text').quicksearch('.yd-menu-search',{
            'bind': $.browser.msie ? 'enter' : 'keyup',
            'delay': 500,
            'show' : function(){
                if ( !checkForAction() ){
                    return;
                }
                $(this).show().addClass('yd-found-sth');
            },
            'hide' : function(){
                if ( !checkForAction() ){
                    return;
                }
                $(this).hide().removeClass('yd-found-sth');
            },
            'onAfter' : function(){
                
                if ( !checkForAction() ){
                    return;
                }
                
                $('.yd-menu-nav2').hide();
                $('.menu-box').each(function(){
                    var countFound = $('.yd-found-sth',this).length;
                    var categoryId = this.id.split('-')[2];
                    if ( countFound > 0 ){
                        log('found ' + countFound + ' matches, showing category');
                        $('.search-no-meal-in-cat',this).hide();                       
                        //show category where sth is found
                        $('#yd-category-' + categoryId).show();
                        
                    }
                    else{
                        log('found no matches, hiding category');
                        $('.search-no-meal-in-cat',this).show();                     
                        //hide category, where nothing is found
                        $('#yd-category-' + categoryId).hide();
                    }
                });
                
                //remove active class, if nothing has been found in this class
                $('.yd-menu-nav1 .active').removeClass('active');
                
            }
        });
    }

});