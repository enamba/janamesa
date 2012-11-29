$(document).ready(function(){
    
    $('.yd-percent').priceFormat({
        prefix: '% ',
        centsSeparator: ',',
        thousandsSeparator: '.'
    }); 

    /**
     * @author vpriem
     * @since 17.07.2012
     */
    $("ul.be-tabs a").each(function() {
        var location = window.location.href;
        if (location.indexOf("?") > -1) {
            location = location.split('?')[0];
        }
        var href = this.href;
        if (location == href) {
            $(this).addClass("active");
            return false;
        }
    });

    /**
     * @author vpriem
     * @since 18.07.2012
     */
    $('div.yd-tab').hide().eq(0).show();
    $('a.yd-tab').live('click', function(){
        $('a.yd-tab').removeClass('active');
        $(this).addClass('active');
        $('div.yd-tab').hide();
        $(this.hash).show();
        this.blur();
        return false;
    }).eq(0).addClass('active');

    /**
     * @author mlaug
     * @since 01.07.2011
     */
    if ($('.yd-datepicker-default').length > 0) {
        initDatepicker('default', '.yd-datepicker-default');
    }
    
    /**
     * jQuery UI CSS only for Datepicker
     * @author oknoblich
     * @since 08.09.2011
     */
    $('#ui-datepicker-div').wrap('<div class="yd-jquerycss-prison"></div>');
    
    /**
     * jQuery Grid Box Design Hack for bottom position
     * @author oknoblich
     * @since 08.09.2011
     */

    $('.yd-grid-box').before('<br /><br />');
    
    /**
     * Grid Box Image Positioning
     * @author oknoblich
     * @since 10.07.2012
     */
    
     $('td:has(img.yd-state-center)').addClass('center');
    
});