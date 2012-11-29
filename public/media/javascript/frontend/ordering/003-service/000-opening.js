$(document).ready(function(){
   
   /**
     * Show service open in
     * @author vpriem
     * @since 25.08.2011
     */
    if (typeof services !== "undefined") {
        // Get server time and use it in Brazil instead of browser time
        var dateStr;
        if(typeof(LOCALE) != 'undefined' && LOCALE == 'pt_BR') {
            var req = new XMLHttpRequest();
            req.open('head', '/', false);
            req.send(null);
            dateStr = req.getResponseHeader('Date');
        }
        if(dateStr) {
            var nowDate = new Date(Date.parse(dateStr));
        } else {
            var nowDate = new Date();
        }
        var now = nowDate.getTimestampSinceMidnight();
        
        $('.yd-service-container').each(function(){
            var id = this.id.split('-')[2];
            var openings = false;
            var index;
            
            $.each(services, function(i, service){
               if (service.id == id) {
                   index = i;
                   openings = service.openings;
                   return false;
               }
            });
            
            if (openings === false || openings === undefined) {
                return;
            }
            services[index].open = 99999;
            
            if (!openings.length) {
                $(this).data('open', false);
                $('.yd-service-open', this).hide();
                $(".yd-service-open-in", this)
                    .html($.lang("is-closed"))
                    .show();
                return;
            }

            var msg = false;
            for (var i = 0; i < openings.length; i++) {
                var from = new Date().setTimestamp(openings[i][0]).getTimestampSinceMidnight();
                var until = new Date().setTimestamp(openings[i][1]).getTimestampSinceMidnight();
                
                if (now < from) {
                    var m, h;
                    var diff = from - now;
                    diff = Math.ceil(diff / 60) * 60;
                    if (diff < 3600) {
                        m = Math.ceil(diff / 60);
                        msg = $.lang("open-in", $.nlang("minute", "minutes", m, m));
                    }
                    else {
                        h = Math.floor(diff / 3600);
                        m = Math.ceil(diff % 3600 / 60);
                        msg = $.lang("open-in", $.nlang("hour", "hours", h, h));
                        if (m) {
                            msg += " " + $.nlang("minute", "minutes", m, m);
                        }
                    }
                    services[index].open = diff;
                    break;
                }
                else if (now > until) {
                    msg = $.lang("is-closed");
                    services[index].open = 86400;
                    // never break
                }
                else { // we are between from and until
                    msg = false;
                    services[index].open = 0;
                    break;
                }
            }
            
            if (msg === false) {
                $(this).data('open', true);
                return;
            }
            
            $(this).data('open', false);
            $('.yd-service-open', this).hide();
            $(".yd-service-open-in", this)
                .html(msg)
                .show();
        });
    }
});
