$(document).ready(function(){

    /**
     * @author vpriem
     * @since 10.02.2011
     */
    $('a.yd-index-slider').live('mouseover', function(){
        $('a.yd-index-slider').removeClass('active');
        $('ul.yd-index-slider-content').fadeOut('fast');
        var id = this.id.split('-')[3];
        $('#yd-index-slider-' + id).addClass('active');
        $('#yd-index-slider-' + id + '-content').fadeIn('fast');
        return false;
    });
    
});
