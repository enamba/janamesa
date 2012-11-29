/**
 * Tracking Pixel Adcloud Service and 12 Monkey Retargeting
 * called in 003-reachable
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 24.10.2011
 * @maybe TODO check referer 
 */
function trackServices() {
   
    if (($("body.de").length || $("body.de2").length) && !$('body.eat-star').length) {
        var serviceCount = $('#yd-filter-found').children();
        if (serviceCount.length){
            //Tracking for 12 Monkeys
            if (typeof track12Monkeys == "function" ){
                  track12Monkeys();          
            }
          
            // Tracking für Criteo
            if (typeof trackCriteo == "function") {
                var first = serviceCount[0].id.split('-')[2];
                var second = (serviceCount[1])? serviceCount[1].id.split('-')[2] : 0;
                var third = (serviceCount[2])? serviceCount[2].id.split('-')[2] : 0;

                trackCriteo(first , second, third);
            }
            
        } else {
            if(typeof trackMediaPlexPyszne == "function") {
                trackMediaPlexPyszne();
            }
        }
    }
}
