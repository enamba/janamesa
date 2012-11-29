/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 21.05.2012
 * @return ydRecurring
 */
var ydRecurring = {

    _lastorder: null,
    _lastarea: null,
    _lastorderarea: null,

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    init: function() {
        this._lastorder = null;
        this._lastarea = null;
        this._lastorderarea = null;
        return this;
    },
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    setLastOrder: function(hash){
        log('setting lastorder in recurring cookie to ' + hash);
        this._lastorder = hash;
        return this.save();
    },
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    setLastOrderArea: function(area){
        log('setting lastorderarea in recurring cookie to ' + area);
        this._lastorderarea = area;
        return this.save();
    },
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    setLastArea: function(area){
        log('setting lastarea in recurring cookie to ' + area);
        this._lastarea = area;
        return this.save();
    },
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    loadLastOrder: function(){
        if (this._lastorder !== null) {     
            var $lastorder = $('#yd-my-last-order');
            if ($lastorder.length) {                
                $.ajax({
                    url: '/request_order/lastorder',
                    data: {
                        hash: this._lastorder,
                        mode: ydState.getMode(),
                        kind: ydState.getKind()
                    },
                    success: function (html) {
                        if (html.length) {
                            $lastorder
                            .html(html)
                            .fadeIn('slow');
                        }
                    }
                });
            }
        }
    },
    
    /**
     * inserting last area plz into autocomplete. If an order has been placed
     * we use that one instead of the last type one
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.05.2012
     */
    loadLastArea: function(){
        var lastarea = this._lastorderarea !== null ? this._lastorderarea : this._lastarea;
        if (lastarea !== null) {
            log('found an area from recurring cookie: ' + lastarea);
            $('form[name="plzAutocomplete"]').each(function(){
                var plzInput = this.plz;
                plzInput.value = lastarea;
                setTimeout(function(){
                    $(plzInput).trigger('keydown'); //trigger autocomplete
                }, 1000);
                $(plzInput).bind('keyup', function(e){
                    log('new input, clearing inserted value');
                    this.value = String.fromCharCode(e.keyCode ? e.keyCode : e.which);
                    $(this).unbind('keyup'); 
                });
            });
        }
    },

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    read: function(){
        var state = $.cookie('yd-recurring');
        if (state !== null) {
            state = decodeURIComponent($.base64.decode(state)).split("#");
            this._lastorder = state[0] ? state[0] : null;
            this._lastarea = state[1] ? state[1] : null;
            this._lastorderarea = state[2] ? state[2] : null;
        }
        return this;
    },

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return ydRecurring
     */
    save: function(){
        $.cookie('yd-recurring', $.base64.encode(encodeURIComponent([
            this._lastorder,
            this._lastarea,
            this._lastorderarea
            ].join("#"))), {
            path: '/'
        });
        return this;
    }

};

ydRecurring.read();
$(document).ready(function(){
    ydRecurring.loadLastArea();
});