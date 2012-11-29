/**
 * @author mlaug
 * @since 12.07.2011
 */
var ydCustomer = {

    _name: null,
    _prename: null,
    _company: null,
    _companyId: 0,
    _admin: 0,
    _hasLocations: 0,

    /**
     * @author vpriem
     * @since 23.08.2011
     * @return ydCustomer
     */
    init: function() {
        this._name = null;
        this._prename = null;
        this._company = null;
        this._companyId = 0;
        this._admin = 0;
        this._hasLocations = 0;
        return this.save();
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @param string name
     * @return ydCustomer
     */
    setName: function(name){
        this._name = name;
        return this.save();
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @return string
     */
    getName: function(){
        return this._name;
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @param string prename
     * @return ydCustomer
     */
    setPrename: function(prename){
        this._prename = prename;
        return this.save();
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @return string
     */
    getPrename: function(){
        return this._prename;
    },

    /**
     * @author vpriem
     * @since 25.07.2011
     * @return string
     */
    getFullname: function(){
        if (this._prename && this._name) {
            return this._prename + ' ' + this._name;
        } else {
            return (this._prename)? this._prename: this._name;
        }
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @param string company
     * @return ydCustomer
     */
    setCompany: function(company){
        this._company = company;
        return this.save();
    },

    /**
     * @author mlaug
     * @since 12.07.2011
     * @return string
     */
    getCompany: function(){
        return this._company;
    },

    /**
     * @author mlaug
     * @since 28.09.2011
     * @return ydCustomer
     */
    setCompanyId: function(id){
        this._companyId = id;
        return this.save();
    },

    /**
     * @author mlaug
     * @since 28.09.2011
     * @return string
     */
    getCompanyId: function(){
        return this._companyId;
    },

    /**
     * @author vpriem
     * @since 25.07.2011
     * @param int admin
     * @return ydCustomer
     */
    setAdmin: function(admin){
        this._admin = admin ? 1 : 0;
        return this.save();
    },

    /**
     * @author vpriem
     * @since 25.07.2011
     * @return int
     */
    isAdmin: function(){
        return this._admin;
    },

    /**
     * @author jens naie <naie@lieferando.de>
     * @since 20.08.2012
     * @param int hasLocations
     * @return ydCustomer
     */
    setHasLocations: function(hasLocations){
        this._hasLocations = hasLocations ? 1 : 0;
        return this.save();
    },

    /**
     * @author jens naie <naie@lieferando.de>
     * @since 20.08.2012
     * @return int
     */
    hasLocations: function(){
        return this._hasLocations
    },

    /**
     * @author mlaug
     * @since 11.07.2011
     * @return ydCustomer
     */
    read: function(){
        var state = $.cookie('yd-customer');
        if (state !== null) {
            state = decodeURIComponent($.base64.decode(state)).split("#");
            this._name = state[0] ? state[0] : null;
            this._prename = state[1] ? state[1] : null;
            this._company = state[2] ? state[2] : null;
            this._admin = parseInt(state[3]) ? 1 : 0;
            this._companyId = parseInt(state[4]) ? parseInt(state[4]) : 0;
            this._hasLocations = parseInt(state[5]) ? 1 : 0;
        }
        return this;
    },

    /**
     * @author mlaug
     * @since 11.07.2011
     * @return ydCustomer
     */
    save: function(){
        $.cookie('yd-customer', $.base64.encode(encodeURIComponent([
                this._name,
                this._prename,
                this._company,
                this._admin,
                this._companyId,
                this._hasLocations
            ].join("#"))), {
            path: '/'
        });
        return this;
    }

};
ydCustomer.read();