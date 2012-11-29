/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Change Zend Errors to Normal
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 14.05.2012
 */
function ZendError2Normal() {
    
                       
    var errors = $('.zend_form .errors');
    
    $(errors).each(function(index, elem){
                      
        var content =$($(elem).children()[0]).text();
        
        var errorBase = "<div id=\"zend-error-"+index +"\" class=\"formError nameformError\" style= \"display:none; top: -30px; left: 140px\">" +
        "<div class=\"formErrorContent\">" + content + "</div>" +
        "<div class=\"formErrorArrow\">" +
        "<div class=\"line10\"></div>" +
        "<div class=\"line9\"></div>" +
        "<div class=\"line8\"></div>" +
        "<div class=\"line7\"></div>" +
        "<div class=\"line6\"></div>" +
        "<div class=\"line5\"></div>" +
        "<div class=\"line4\"></div>" +
        "<div class=\"line3\"></div>" +
        "<div class=\"line2\"></div>" +
        "<div class=\"line1\"></div>" +
        "</div>" +
        "</div>" +   
        "</div>";
    
        var element = $(elem).prev();
                
        
        $(element).parent().append(errorBase);
        
        var errorInDom = $('#zend-error-'+index);
        
        $(errorInDom).css('top',$(element).position().top - 45 );
        $(errorInDom).css('left',$(element).position().left + $(element).width() - 60 );
        
        $('.formError').show();
        $(element).addClass('yd-form-invalid');        


    });
    
    
    $('body').on( 'click','.formError', function() {
        $('.formError').hide();
    } );
        
                       
}



$(document).ready( function() {
    if ( $('#filter_Datumgrid').length > 0){
        initDatepicker('full', 'filter_Datumgrid');
    }
    
    ZendError2Normal();
    
    
    
    
    
});


$(document).ready(function(){
    $('.accordion li a.accordion-toggle').bind("click", function() {
        $(this).toggleClass('active');
        $(this).next().slideToggle();
        return false;
    }).next().hide();
    
    $('span.showfaq').bind("click", function() {
        var $element = $(this).attr("id").split("-")[1];
        var $toggle = $(".accordion li a.accordion-toggle");
        
        $toggle.next().hide();
        $toggle.removeClass('active');
        
        $toggle.eq($element-1).toggleClass('active');
        $toggle.eq($element-1).next().slideToggle();
        return false;
    }).next().hide();
});

