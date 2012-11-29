$(document).ready(function(){
    /**
     * expand help text for minamount in sidebar
     * @author oknoblich
     * @since 25.02.2011
     */
    $('#minamount').live('hover', function(){
        $('#yd-minamount-toogle-out').fadeToggle();
        return false;
    });

});