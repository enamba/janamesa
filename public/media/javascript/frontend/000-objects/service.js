/**
 * @author vpriem
 * @since 21.12.2011
 */
var ydServicePrototype = {
    
    /**
     * @author vpriem
     * @since 21.12.2011
     * @return boolean
     */
    isOpen: function(){
        
        if (this.type != "rest") {
            return false;
        }
        
        if (!this.openings.length) {
           return false; 
        }
        
        var now = new Date().getTimestampSinceMidnight();
        
        for (var i = 0; i < this.openings.length; i++) {
            var from = new Date().setTimestamp(this.openings[i][0]).getTimestampSinceMidnight();
            var until = new Date().setTimestamp(this.openings[i][1]).getTimestampSinceMidnight();

            if (now < from) {
                // never break
            }
            else if (now > until) {
                // never break
            }
            else { // we are between from and until
                return true;
            }
        }

        return false;
    },
    
    
    /**
     * Check if Service is Closing in defined Time
     * @var timeToClose Time in seconds
     * @author dhahn
     * @since 10.05.2012
     */
    isClosing: function(timeToClose) {
        
        if (!timeToClose) {
            timeToClose = 15*60;
        }
                   
        if (!this.openings.length) {
           return false; 
        }
        
        if (!this.isOpen()) {
            return false;
        }
        
        var now = new Date().getTimestampSinceMidnight();
        
        for (var i = 0; i < this.openings.length; i++) {
            var from = new Date().setTimestamp(this.openings[i][0]).getTimestampSinceMidnight();
            var until = new Date().setTimestamp(this.openings[i][1]).getTimestampSinceMidnight();
            
            log("time until:"+ until);
            
            // hack for Openings until next day, 86399 is 23:59:59
            if (until == 86399) {
                var firstFrom  = new Date().setTimestamp(this.openings[0][0]).getTimestampSinceMidnight();
                log("tmp: " + firstFrom);                
                if (firstFrom == 0) {
                    var firstUntil = new Date().setTimestamp(this.openings[0][1]).getTimestampSinceMidnight();
                    until = until + firstUntil;
                    log("final until: " + until);
                }
            }     
            
            if (now < from) {
                // never break;
            }
            else if (now > until) {
                // never break
            }
            else if (now + timeToClose > until) {
                return true;
            }
        }
        
        return false;
        
    }
    
};




/**
 * @author vpriem
 * @since 21.12.2011
 */
function YdService(id, type) {
    
    var service = {
        id: id,
        type: type || "rest",
        openings: []
    };
    
    if (typeof services !== "undefined") {
        $.each(services, function(i, s){
           if (s.id == service.id && s.type == service.type) {
               return $.extend(service, s, ydServicePrototype);
           }
        });
    }
    
    return $.extend(service, ydServicePrototype);
}