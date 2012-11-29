/**
 * @author Matthias Laug <laug@lieferando.de>
 */

var socket;
$(document).ready( function() {
    
    if ( partnerOrderTickerEnabled ){
        
        function cleanupOrderTicker(nr){
            log('cleaning up order ' + nr);
            $('#partner-order-' + nr).remove();
            if( $('.yd-order-notification ul').length == 0 ){
                $('.yd-order-notification').hide();
            }
        }

        $('.yd-order-notification').hide();

        if (typeof io !== 'undefined' ){

            var socket = io.connect(ordertickerUrl + ':' + ordertickerPort);
            var now = new Date();

            //register our service to the node js module and call for all orders
            setInterval(function(){
                log('calling for orders after ' + now.format('yyyy-mm-dd H:MM:s'));
                socket.emit('orders', { 
                    serviceId : partnerServiceId,
                    time : now.format('yyyy-mm-dd H:MM:s')
                });
            }, 10000);

            //make a beep, if here is a new order
            socket.on('orders', function (data) {      
                //showing info box and append order
                log('order ' + data.orderId + ' has arrived, making a beep');
                $('.yd-order-notification').show();

                var appendSound = '';
                if ( partnerOrderTickerSound ){
                    appendSound = "<embed src='http://cdn.yourdelivery.de/audio/partner/notification-bell-96.mp3' hidden=true autostart=true loop=false>";
                }

                var orderTime = new Date(data.time);
                var html = "\n\
                        <ul id='partner-order-" + data.nr + "'>\n\
                            <li><span>Nr:</span><a href='/overview/orders#" + data.nr + "'>" + data.nr + "</a></li>\n\
                            <li><span>Bestellung vom:</span> " + orderTime.format('dd.mm  H:MM') + "</li>\n\
                            <li><span>Betrag:</span> " + int2price(data.amount, true) +"</li>\n\
                            <li><span>Bezahlart:</span> " + data.payment +"</li>\n\
                            <li><span>Rabatt:</span> " + int2price(data.discount, true) +"</li>\n\
                        </ul>" + appendSound;
                $('#yd-incoming-orders').append(html);        


                //inform server, this id has been process
                socket.emit('done', {
                    orderId : data.orderId
                });

                //remove after 10 seconds
                setTimeout(cleanupOrderTicker, 10000, data.nr);
            });

        }
        else{
            log('could not init io socket interface');
        }
    }
});