/**
 * @author vpriem
 * @since 11.07.2011
 */
var ydState = {

    _city: null,
    _location: null,
    _kind: "priv",
    _mode: "rest",
    _number: 0,
    _verbose: null,

    /**
     * @author vpriem
     * @since 23.08.2011
     * @return ydCustomer
     */
    init: function() {
        this._city = null;
        this._location = null;
        this._kind = "priv";
        this._mode = "rest";
        this._verbose = null;
        return this.save();
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @param int city
     * @return ydState
     */
    setCity: function (city) {
        if (typeof ydCurrentRanges !== "undefined") {
            if (ydCurrentRanges[city] !== undefined) {
                var range = ydCurrentRanges[city];

                $("a.yd-set-city-url").attr("href", "/" + range.restUrl);

                var m =  range.deliverTime / 60;
                if (m < 60) {
                    $(".yd-deliver-time").html($.nlang("minute", "minutes", m, m));
                    $(".yd-menu-handling-time").html($.nlang("minute", "minutes", m, m));
                }
                else {
                    m = parseInt(m / 60);
                    $(".yd-deliver-time").html($.nlang("hour", "hours", m, m));
                    $(".yd-menu-handling-time").html($.nlang("hour", "hours", m, m));
                }

                $('span.yd-set-city-text').html(range.name);
                $('.yd-set-city-url').removeClass('hidden');

                if (typeof ydOrder !== "undefined") {
                    ydOrder.set_deliver_cost(range.deliverCost, range.noDeliverCostAbove)
                           .set_min_amount(range.minCost);
                }
            }
            else {
                return false;
            }
        }

        this._city = city;
        log("CITY:"+city);

        return this.save();
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return string
     */
    getCity: function(){
        return this._city;
    },

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.05.2012
     */
    setVerbose: function(verbose){
        this._verbose = verbose;
        return this.save();
    },
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.05.2012
     * @return integer
     */
    getVerbose: function(){
        return this._verbose;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @param int location
     * @return ydState
     */
    setLocation: function (location) {
        this._location = location;
        return this.save();
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return string
     */
    getLocation: function(){
        return this._location;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @param string kind priv|comp
     * @return ydState
     */
    setKind: function (kind) {
        this._kind = kind;
        log('change kind to ' + kind);
        return this.save();
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return string
     */
    getKind: function(){
        return this._kind;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return string
     */
    getMode: function(){
        return YdMode;
    },

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.12.2011
     * @return integer
     */
    getNumber: function(number){
        return this._number;
    },

    /**
     * @author Matthias Laug <laug@lieferandode.>
     * @since 15.12.2011
     * @param integer
     */
    setNumber: function(number){
        this._number = number;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return boolean
     */
    maybeLoggedIn: function(){
        var check =  $.cookie('YD_UID') !== null && (
                     ydCustomer.getName() !== null ||
                     ydCustomer.getPrename() !== null);
        if ( !check ){
            $.cookie('YD_UID',null);
        }
        return check;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return ydState
     */
    read: function(){
        var state = $.cookie('yd-state');
        if (state !== null) {
            state = $.base64.decode(state).split("#");
            this._city = state[0] ? state[0] : null;
            this._location = state[1] ? state[1] : null;
            this._kind = state[2] ? state[2] : "priv";
            this._mode = state[3] ? state[3] : "rest";
            this._number = state[4] ? state[4] : 0;
            this._verbose = state[5] ? state[5] : null;
        }
        return this;
    },

    /**
     * @author vpriem
     * @since 11.07.2011
     * @return ydState
     */
    save: function(){
        $.cookie('yd-state', $.base64.encode([this._city, this._location, this._kind, this._mode, this._number, this._verbose].join("#")), {
            path: '/'
        });
        return this;
    }
};
ydState.read();