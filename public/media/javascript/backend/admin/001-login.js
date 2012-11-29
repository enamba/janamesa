$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 16.06.2011
     */
    $("#login").each(function(){
        if (!this.user.length) {
            this.user.focus();
        }
        else {
            this.pass.focus();
        }
    });
    
});