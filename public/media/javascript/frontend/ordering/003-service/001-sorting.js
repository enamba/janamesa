/**
 * sort a json array by a given field
 * @author http://stackoverflow.com/questions/979256/how-to-sort-a-json-array
 * @since 26.02.2011
 */
var sort_by = function (field, reverse, primer) {
    reverse = reverse ? -1 : 1;

    return function (a1, b1) {
        a = a1[field];
        b = b1[field];
        
        if (typeof(primer) != 'undefined'){
            a = primer(a);
            b = primer(b);
        }

        if (a < b) return reverse * -1;
        if (a > b) return reverse * 1;
        if(a == b) {
            c = a1['ratingcount'];
            d = b1['ratingcount'];
            
            if (c < d) return reverse * -1;
            if (c > d) return reverse * 1;
            return 0;
        }
       
    };
};

/**
 * reset entire search list
 * @author mlaug
 * @since 30.08.2011
 */
function _resetSearchList(){
    
    // delete cookies
    $.cookie('yd-sorting', null);
    $.cookie('yd-sorting-category', null);
    
    // reset the field
    $('.ordering-category-no').hide();
    $('.yd-service-menu-results').html('<tbody><tr class="even hidden"><td colspan="3"></td></tr></tbody>');
    
    // reset search to alpha
    _doSort($('#sort-alpha')[0], "alpha", false);
}

/**
 * sorting of metrics
 * @author mlaug, vpriem
 * @since 24.06.2011
 */ 
function _doSort(_this, field, revert){
    var sorted = services;
    if (field == "tipp") {
        sorted = sorted.sort(sort_by("ratingstars", true));
        sorted = sorted.sort(sort_by("score", true));
    } else {
        sorted = sorted.sort(sort_by(field, revert));        
    }

    for (var i = 0; i < sorted.length; i++) {
        var $s = $('#yd-service-' + sorted[i].id + '-' + sorted[i].type);
        if ($s.length) {
            if ($s.data('open')) {
                $("#yd-filter-found").append($s);
            }
            else{
                $("#yd-filter-found-closed").append($s);
            }
        }
    }

    toggleActiveSorting(_this);
    $.cookie('yd-sorting', field + '#' + revert, {
        expires: 7
    });
    $.cookie('yd-sorting-category', null);
    $('.ordering-category-no').hide();
}

/**
 * sorting of metrics
 * @author mlaug, vpriem
 * @since 24.06.2011
 */ 
function _doSortCategory(_this, categoryId){
    log('initializing category sort');
    for (var i = 0; i < services.length; i++) {
        var $s = $('#yd-service-' + services[i].id + '-' + services[i].type);
        if (!$s.length) {
            log('no div element found for ' + services[i].id);
            continue;
        }
        if (services[i].category == categoryId) {
            if ($s.data('open')) {
                $("#yd-filter-found").append($s);
            }
            else{
                $("#yd-filter-found-closed").append($s);
            }
        }
        else {
            $("#yd-filter-the-rest").append($s);
        }
    }
        
    $.cookie('yd-sorting-category', categoryId, {
        expires: 7
    });
    $.cookie('yd-sorting', null);
    toggleActiveSorting(_this);
    $('.ordering-category-no').show();
}

/**
 * sorting of metrics
 * @author mlaug, vpriem
 * @since 24.06.2011
 */ 
function _doSortComida(comida){
    log('initializing category comida');
    foundMeal = false;
    for (var i = 0; i < services.length; i++) {
        var $s = $('#yd-service-' + services[i].id + '-' + services[i].type);
        if (!$s.length) {
            log('no div element found for ' + services[i].id);
            continue;
        }
        
        tagName = replaceAccents(services[i].tags);
        comida = comida.toLowerCase();
        tagNames = tagName.split(/, /);
        log(tagNames);
        log(comida);
        if (jQuery.inArray(comida, tagNames)>=0) {
            foundMeal = true;
            if ($s.data('open')) {
                $("#yd-filter-found").append($s);
            }
            else{
                $("#yd-filter-found-closed").append($s);
            }
        }
        else {
            $('#yd-filter-the-rest-mesg').removeClass('hidden');
            $("#yd-filter-the-rest").append($s);
        }
    }
    if (foundMeal == false){
        $('#yd-filter-the-rest-mesg').removeClass('hidden');
        $('#yd-filter-the-rest-mesg').addClass('error');
        $('#yd-filter-the-rest-mesg p').html("UUUPS! Infelizmente n&atilde;o encontramos em sua regi&atilde;o restaurantes da especialidade escolhida. Mas temos algumas sugest&otilde;es para matar sua fome:");
    }
}

function replaceAccents (text) {
    text = text.replace(new RegExp('[ÁÀÂÃ]','gi'), 'A');
    text = text.replace(new RegExp('[ÉÈÊ]','gi'), 'E');
    text = text.replace(new RegExp('[ÍÌÎ]','gi'), 'I');
    text = text.replace(new RegExp('[ÓÒÔÕ]','gi'), 'O');
    text = text.replace(new RegExp('[ÚÙÛ]','gi'), 'U');
    text = text.replace(new RegExp('[Ç]','gi'), 'C');
    text = text.toLowerCase();
    return text;
}

/**
 * toggle the marker for the active li
 * @author mlaug
 * @since 26.02.2011
 */
function toggleActiveSorting(elem){ 
    $('#yd-sorting-middle-list ul li.active').removeClass('active');
    var $elem = $(elem);
    if ($elem.length) {
        $elem.addClass('active');
        $('.currently-sorted-by').html('"' + $elem.html() + '"');
    }
}

/**
 * @author mlaug
 */
$(document).ready(function(){
    if ($.browser.msie && $.browser.version.split('.')[0] < 9) {
        return;
    }

    if (typeof(services) != 'undefined' && $(".yd-service-page").length) {
        var preSorting = $.cookie('yd-sorting');
        var preSortingCategory = $.cookie('yd-sorting-category');
        if (filtroPorComida) {
            _doSortComida(filtroPorComida);
        } else if (preSortingCategory) {
            _doSortCategory($('#sort-' + preSortingCategory), preSortingCategory);
        }
        else if (preSorting) {
            var field = preSorting.split('#')[0];
            var revert = preSorting.split('#')[1] == 'false' ? false : true;
            _doSort($('#sort-' + field), field, revert);
        }
        else {
            _doSort($('#sort-open'), "tipp", false);
        }
    
        $('.do-sort').live('click', function(){      
            //services is defined in the service template
            var field = this.id.split('-')[1];
            var revert = $(this).hasClass('revert');             
            _doSort(this, field, revert);
            
            /**
             * tracking for criteo
             * @author Daniel Hahn <hahn@lieferando.de>
             */                        
            log('sorting finished ...');
            if (typeof trackCriteo == "function") {
                trackCriteo(services[0] !== undefined ? services[0].id : 0, 
                            services[1] !== undefined ? services[1].id : 0,
                            services[2] !== undefined ? services[2].id : 0);
            }
        });
    }
    
    //sort by category
    $('.do-sort-category').live('click', function(){
        var categoryId = this.id.split('-')[1];
        _doSortCategory(this, categoryId);
    });
    
    //reset everything
    $('#yd-reset-service-filter').live('click', function(){
        _resetSearchList();
    });
    
    $('.yd-sorting-middle-dropdown, .yd-sorting-middle-dropdown-box').live('click', function(){
        $('#yd-sorting-middle-list').toggle();
        $('#yd-sorting-middle').toggleClass('active');
        return false;
    });
   
});