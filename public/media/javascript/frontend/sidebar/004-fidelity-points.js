$(document).ready(function(){
    /**
     * expand help text for fidelity points in sidebar
     * @author mlaug
     * @since 05.01.2010
     */
    $('#how-fidelity-works').live('hover',function(){
        $('#how-fidelity-works-content').slideToggle('fast');
    });

});