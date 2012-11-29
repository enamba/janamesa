$(document).ready(function(){
    
    /**
     * Set mode
     * @author vpriem
     * @since 12.07.2011
     */
    if (typeof ydState !== "undefined") {
        
        $("a.yd-go-to-start").click(function(){
            if (ydState.maybeLoggedIn()) {
                var mode = this.id.split("-")[2];
                
                var url = "/order_private/start";
                if (ydState.getKind() == "comp") {
                    url = "/order_company/start";
                }
                location.href = url + "/mode/" + mode;
                return false;
            }
        });
    }

});