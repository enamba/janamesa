$(document).ready(function(){
    
    $('.yd-money').priceFormat({
        limit: 5,
        prefix: 'EUR ',
        centsSeparator: ',',
        thousandsSeparator: '.'
    }); 
    
    if ($.fn.formToggle) {
        $("form.yd-form-toggle").formToggle();
        $("a.yd-form-toggle").click(function(){
            $(this)
            .blur()
            .closest("form")
            .formToggle();
            return false;
        });
    }
    
    /**
     * jQuery Grid Box Design Hack for bottom position
     * @author oknoblich
     * @since 08.09.2011
     */

    $('.yd-grid-box').before('<br /><br />');

});