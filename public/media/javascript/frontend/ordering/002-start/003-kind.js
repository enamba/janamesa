$(document).ready(function(){
    
    /**
     * Set mode
     * @author vpriem
     * @since 12.07.2011
     */
    if (typeof ydState !== "undefined") {
        
        $("a.yd-set-kind-priv").click(function(){
            ydState.setKind("priv");
        });
        
        $("a.yd-set-kind-comp").click(function(){
            ydState.setKind("comp");
        });
    }

});