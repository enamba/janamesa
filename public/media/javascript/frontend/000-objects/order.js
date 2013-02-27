/**
 * @hint put no functions in here, but in ydOrderPrototype
 * this object will be stored in html5 storage and will not store
 * any functions. Those will be extended onload
 */
var ydOrder = {
    
    version: "1.2",
    service_id: 0,
    
    link_to_menu: null,
    
    //store any charge for paypal, credit here
    charge: 0,

    min_amount: 0,
    deliver_cost: 0,
    no_deliver_cost_above: 0,
    
    //discount
    discount : null,

    //store all the meals in here
    meals: {}
};

var ydOrderPrototype = {
    
    /**
     * Set service id
     * @author vpriem
     * @since 28.07.2011
     * @return ydOrder
     */
    set_service: function(service_id){
        this.service_id = parseInt(service_id);
        return this;
    },
    
    /**
     * Set min amount
     * @author vpriem
     * @since 11.07.2011
     * @return ydOrder
     */
    set_min_amount: function(amount){
        this.min_amount = parseInt(amount);
        $(".yd-min-amount, .yd-delivery-from").html(int2price(amount, true));      
        this.update_view_amounts();
        return this;
    },
    
    add_discount: function(discount){
        this.discount = discount;
        this.discount.cart_total = parseInt(this.calculate_amount());
        log("Discount Cart-total: " + this.discount.cart_total);
        this.check_payment();
    },
    
    remove_discount: function(){
        this.discount = null;
        this.check_payment();
        this.update_view_amounts();
    }, 
    
    check_payment: function(){
        var openAmount = this.calculate_open_amount();
        if (openAmount <= 0) {
            log("open amount <= 0 - hiding paymentcontent");
            $('#paymentcontent').hide();
            $('.yd-finish-payment').hide();
        }
        else {
            log("open amount > 0 - showing paymentcontent");
            $('#paymentcontent').show();
            $('.yd-finish-payment').show();
            
            if (openAmount <= 10) {
                log("open amount <= 10 - hiding ebanking");
                $('#yd-finish-payment-ebanking').hide();
            }
        }
    },
    
    /**
     * calculate the discount amount
     * @author mlaug
     * @since 08.07.2011 
     */
    get_discount: function(format){
        if ( this.discount === null ){
            if ( format ){
                return int2price(0, true);
            }
            return 0;
        }
        var amount = this.discount.get_amount();
        if (format === true) {
            return int2price(amount, true);
        }
        return amount;
    },
    
    /**
     * Set deliver cost
     * @author vpriem
     * @since 11.07.2011
     * @return ydOrder
     */
    set_deliver_cost: function(cost, noDeliverAbove){      
        this.deliver_cost = parseInt(cost);
        this.no_deliver_cost_above = noDeliverAbove;
        this.update_view_deliver_cost();
        this.update_view_amounts();
        return this;
    },
    
    /**
     * get deliver cost and keep in mind, that we may
     * have an upper limit
     * @author mlaug
     * @since 16.08.2011
     * @return integer
     */
    get_deliver_cost: function(total){
        if ( !total ){
            total = this.get_meal_amount();
        }
        var floorFeeCost = this.get_floorfee_cost();
        if ( this.no_deliver_cost_above == 0 || this.no_deliver_cost_above > total ){
            return this.deliver_cost + floorFeeCost;
        }  
        
        if(floorFeeCost > 0){
            log('found floorFeeCost: '+floorFeeCost);
            return floorFeeCost;
        }
        
        log('upper bound ' + this.no_deliver_cost_above + ' reached, returning 0 deliver cost');
        return 0;
    },
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.09.2011
     */
    get_floorfee_cost: function(){
        var floor = $('input[name=floor]').val();
        var floorfee = $('input[name=floorfee]').val();
        var mealCountTotal = this.get_meal_count_total();
        
        if (floor === undefined || floor == 'lift') {
            return 0;
        }

        if (floorfee < 1) {
            return 0;
        }
        
        return  floorfee * floor * mealCountTotal;
    },
    
    /**
     * get amount of all meals, just an helper
     * @author mlaug
     * @since 16.08.2011
     * @return integer
     */
    get_meal_amount: function(){
        var total = 0;
        $.each(this.meals, function(k, v){
            total += v.getAllCost();    
        });
        return total;
    },
    
    /**
     * calculate the full amount
     * @author mlaug
     * @since 08.07.2011 
     */
    calculate_amount: function(format){
        var total = 0;
        
        total += this.get_meal_amount();
        
        //add deliver cost, which is set in 006-triggers.js
        log("Deliver Cost: " +  this.get_deliver_cost(total));
        total += this.get_deliver_cost(total);
          
        
        if (format === true) {
            return int2price(total, true);
        }
        
        return total;
    },

    
    /**
     * calculate the min amount
     * @author vpriem
     * @since 21.07.2011 
     */
    calculate_min_amount: function(format){
        var total = 0;
        
        $.each(this.meals, function(k, v){
            if (!v.exMinCost || v.exMinCost == 0) {
                total += v.getAllCost();    
            }
        });
        
        if (format === true) {
            return int2price(total, true);
        }
        
        return total;
    },
    
    /**
     * check if the customer reached min amount
     * @author mlaug
     * @since 08.09.2011
     */
    is_minamount_reached: function(){ 
        if ( ydState.getKind() == 'comp' && typeof companyExceptions !== "undefined" ){          
            for(i=0;i<companyExceptions.length;i++){
                log('checking for ' + companyExceptions[i] + ' against ' + ydCustomer.getCompanyId());
                if ( companyExceptions[i] == ydCustomer.getCompanyId() ){
                    log('make an exception for this company, ignoring mincost');
                    return true;
                }
            }
        }
        
        if ( this.calculate_min_amount() == 0 ){
            return false;
        }
        if ( this.discount != null && this.discount.min_amount > this.min_amount){
            log('found an discount and discount min cost is higher as the one from service ');
            return this.calculate_min_amount() >= this.discount.min_amount;
        }
        return this.calculate_min_amount() >= this.min_amount;
    },
    
    /**
     * calculate the open amount
     * @author mlaug
     * @since 08.07.2011 
     */
    calculate_open_amount: function(format){     
        var total = this.calculate_amount() - this.get_discount();
        
        //remove budget
        if ( ydBudget ){
            total -= ydBudget.get_amount();
        }
        
        if ( total <= 0 ){
            total = 0;
        }
        
        if (format === true) {
            return int2price(total, true);
        }
        
        return total;
    },
   
    /**
     * calculate the charged amount for online payment
     * @author mlaug
     * @since 08.07.2011 
     */
    get_charge: function(format){
        if (format === true) {
            return int2price(this.charge, true);
        }
        
        return this.charge;
    },
    
    /**
     * get a meal from hash
     * @author vpriem
     * @since 22.08.2011
     * @param string hash
     * @return ydMeal|null
     */
    get_meal: function(hash){
        if (this.meals[hash] === undefined) {
            return null;
        }
        
        return this.meals[hash];
    },
    
    /**
     * add a meal to the current order
     * @author mlaug
     * @since 08.07.2011
     */
    add_meal: function(meal, hash, updateView){
        if (updateView === undefined) {
            updateView = true;
        }
        
        //get attributes from current meal
        if (hash === undefined || hash === null) {
            hash = "#" + meal.id + meal.size.id;
            $.each(meal.options, function(k, v){
                hash += v.id; 
            });
            $.each(meal.extras, function(k, v){
                hash += v.id;
            });
            hash = sha1(hash);
        }
        
        if (isNaN(meal.count)) {
            meal.count = 1;
        }
        
        if (this.meals[hash] === undefined) {        
            log('adding new meal to current order state ' + meal.id + ' with a count of ' + meal.count);
            this.meals[hash] = meal; 
        }
        else {
            log('update meal ' + meal.id + ' with count ' + meal.count);
            this.meals[hash].count += meal.count;
        }
        
        if (updateView === true) {
            //only in update view, where we are situated on the menu page
            try {
                //track with piwik that this item has been added     
                log('tracking item ' + meal.id);
                _paq.push(['addEcommerceItem',meal.id,meal.name,null,int2price(meal.size.cost, false, 2, '.'),meal.count]);
                _paq.push(['trackEcommerceCartUpdate',int2price(this.calculate_amount(), false, 2, '.')]);
            }
            catch (err) {
            }
            this.update_view();
        }
        
        this.store();
        
        return hash;
    },
    
    /**
     * remove one meal from stack
     * @author mlaug
     * @since 08.07.2011
     */
    remove_meal: function(hash){
        delete this.meals[hash];
        
        this.update_view();
        this.store();
        
        _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
    },
    
    /**
     * @author vpriem
     * @since 08.06.2012
     * @return ydOrder
     */
    clear_bucket: function(){     
        for (hash in this.meals) {
            delete this.meals[hash];
        }
        
        this.update_view();
        this.store();
        
        _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
        
        return this;
    },
    
    /**
     * get count of meals (do not mind how many of each)
     * @author mlaug
     * @since 26.07.2011
     */
    get_meal_count: function(){
        var count = 0;
        $.each(this.meals, function(k,v){
            count += 1;
        });
        return count;
    },
    
    /**
     * get count of meals (including count of each meal)
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.09.2011
     */
    get_meal_count_total: function(){
        var count = 0;
        $.each(this.meals, function(k,meal){
            if ( isNaN(meal.count) ){
                meal.count = 1;
            }
            count += meal.count;
        });
        return count;
    },
    
    /**
     * increase one meal in stack
     * @author mlaug
     * @since 08.07.2011
     */
    increase_meal: function(hash){
        if (this.meals[hash].count < 999) {
            this.meals[hash].count += 1;
        }
        this.update_view();
        this.store();
        _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
    },
    
    /**
     * dcrease one meal in stack
     * @author mlaug
     * @since 08.07.2011
     */
    decrease_meal: function(hash){
        if (this.meals[hash].count <= this.meals[hash].minCount) {
            this.remove_meal(hash);
            this.update_view();
            this.store();
            _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
            return false;
        }
        //it should be already done above, but we leave it here for the case if seom meals have "null" as minCount
        this.meals[hash].count -= 1;
        if (!this.meals[hash].count) {
            this.remove_meal(hash);
        }
        this.update_view();
        this.store();
        _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
    },
    
    /**
     * alter count of meal
     * @author vpriem
     * @since 13.07.2011
     * @return ydOrder
     */
    set_count: function(hash, count){
        count = parseInt(count);
        this.meals[hash].count = count;
        if (!count) {
            this.remove_meal(hash);
        }
        
        if (this.meals[hash].count < this.meals[hash].minCount) {
            this.remove_meal(hash);
        }
        
        this.update_view();
        this.store();
        _paq.push(['trackEcommerceCartUpdate', int2price(this.calculate_amount(), false, 2, '.')]);
        return this;
    },
    
    /**
     * update the entire view with the data
     * of the current order
     * @author mlaug
     * @since 08.07.2011
     */
    update_view: function(){     
        log('calling update_view');
        
        var $pos = $('#yd-shopping-positions').html("");
        
        $.each(this.meals, function(hash, meal){
            log('appending meal ' + hash + ' to current cart');
            $pos.append(meal.getHtml(hash));
        });
        
        $pos.append(
            '<input type="hidden" name="mode" value="' + YdMode + '" />\n\
            <input type="hidden" name="cityId" value="' + ydState.getCity() + '" />'
        );
    
        log('updating values');
            
        this.update_view_deliver_cost();
        this.update_view_amounts();
    },

    update_view_amounts: function(){    
        log('calling update_view_amounts');
        if (this.calculate_amount() > 0) {
            $("#yd-clear-bucket").show();
            $('#emptycart').hide();
        }
        else{
            $("#yd-clear-bucket").hide();
            $('#emptycart').show();
        }
        
        if (this.is_minamount_reached()) {
            $('#minamount').hide();
            $('.yd-finish-order').removeClass('yd-gray-button');
        }
        else{
            $('#minamount').show();
            $('.yd-finish-order').addClass('yd-gray-button');
        }
        
        // new elements to fill in all the amounts needed
        $('.yd-open-amount').html(this.calculate_open_amount(true));
        $('.yd-full-amount').html(this.calculate_amount(true));
        $('.yd-charge').html(this.get_charge(true));  
    },

    /**
     * update the deliver cost seperatly
     * @author mlaug
     * @since 16.08.2011
     */
    update_view_deliver_cost: function(){   
        var cost = this.get_deliver_cost();
        log('updating deliver costs in view: ' + cost);
        
        $(".yd-deliver-cost").html(int2price(cost, true));
        if (cost > 0) {
            $("#delivercost").show();
        }
        else {
            $("#delivercost").hide();
        }
    },

    /**
     * Store this order to storage
     * @author mlaug
     * @since 26.07.2011
     * @return ydOrder
     */
    store: function(){
        //once the view is updated, we store in html5 storage or fallback
        if (this.service_id) {
            log('store order to storage');
            
            this.link_to_menu = location.href;
            try{
                var current = $.Storage.set("order-" + this.service_id + '-' + YdMode, $.base64.encode(JSON.stringify(this)))
                var last = $.Storage.set("order-last", $.base64.encode(JSON.stringify(this)))

                if (current && last) {
                    log('successfully stored order with key: order-' + this.service_id + '-' + YdMode);
                }
                else{
                    log('failed to store order');
                }
            }
            catch ( err ){
                log('failed to store order ' + err);
            }
        }
        else {
            log('no service id received, cannot store order to storage')
        }
        
        return this;
    },
    
    /**
     * Restore this order from storage
     * @author vpriem
     * @since 28.07.2011
     * @return ydOrder
     */
    restore: function(updateView){
        if (!this.service_id) {
            log('no service id received, cannot restore order from storage');
            return this;
        }
            
        if ($("#finishForm").length) {
            log('restore order from form');
            
            var hash = [], id = [], name = [], count = [], cost = [], minCount = [], exMinCost = [], special = [], 
            size = [], sizeName = [];
            $("#finishForm :hidden.yd-order-meal").each(function(index){
                if (index % 9 == 0) {
                    var h = this.id.split("-")[3];
                    hash.push(h);
                    id.push(this.value);
                }
                else if (index % 9 == 1) name.push(this.value);
                else if (index % 9 == 2) cost.push(parseInt(this.value));
                else if (index % 9 == 3) count.push(parseInt(this.value));
                else if (index % 9 == 4) minCount.push(this.value);
                else if (index % 9 == 5) exMinCost.push(this.value);
                else if (index % 9 == 6) special.push(this.value);
                else if (index % 9 == 7) size.push(this.value);
                else if (index % 9 == 8) sizeName.push(this.value);
            });
            var ydMeal;
            for (var i = 0; i < hash.length; i++) {
                ydMeal = new YdMeal();
                ydMeal.id = id[i];
                ydMeal.name = name[i];
                ydMeal.count = parseInt(count[i]);
                ydMeal.minCount = parseInt(minCount[i]);
                ydMeal.exMinCost = parseInt(exMinCost[i]) ? true : false;
                ydMeal.special = special[i];
                ydMeal.size = new YdSize(size[i], sizeName[i], parseInt(cost[i]));
                this.add_meal(ydMeal, hash[i], updateView);
            }
            
            hash = [], id = [], name = [], cost = [];
            $("#finishForm :hidden.yd-order-option").each(function(index){
                if (index % 3 == 0) {
                    var h = this.id.split("-")[3];
                    hash.push(h);
                    id.push(this.value);
                }
                else if (index % 3 == 1) name.push(this.value);
                else if (index % 3 == 2) cost.push(parseInt(this.value));
            });
            var ydOption;
            for (var i = 0; i < hash.length; i++) {
                ydOption = new YdOption(id[i], name[i], cost[i]);
                ydMeal = this.get_meal(hash[i]);
                if (YdMeal !== null) {
                    ydMeal.addOption(ydOption);
                }
            }
            
            hash = [], id = [], name = [], cost = [];
            $("#finishForm :hidden.yd-order-mealoption").each(function(index){
                if (index % 3 == 0) {
                    var h = this.id.split("-")[3];
                    hash.push(h);
                    id.push(this.value);
                }
                else if (index % 3 == 1) name.push(this.value);
                else if (index % 3 == 2) cost.push(parseInt(this.value));
            });
            var ydMealOption;
            for (var i = 0; i < hash.length; i++) {
                ydMealOption = new YdMealOption(id[i], name[i], cost[i]);
                ydMeal = this.get_meal(hash[i]);
                if (YdMeal !== null) {
                    ydMeal.addOption(ydMealOption);
                }
            }
            
            hash = [], id = [], name = [], cost = [], count = [];
            $("#finishForm :hidden.yd-order-extra").each(function(index){
                if (index % 5 == 0) {
                    var h = this.id.split("-")[3];
                    hash.push(h);
                    id.push(this.value);
                }
                else if (index % 5 == 2) name.push(this.value);
                else if (index % 5 == 3) cost.push(parseInt(this.value))
                else if (index % 5 == 4) count.push(parseInt(this.value));
            });
            var ydExtra;
            for (var i = 0; i < hash.length; i++) {
                ydExtra = new YdExtra(id[i], name[i], cost[i], count[i]);
                ydMeal = this.get_meal(hash[i]);
                if (YdMeal !== null) {
                    ydMeal.addExtra(ydExtra);
                }
            }
            
            this.deliver_cost = $('input[name="deliverCost"]').intVal() + this.get_floorfee_cost();       
            if ( ydCurrentRanges ){
                this.no_deliver_cost_above = parseInt(ydCurrentRanges[$('input[name="cityId"]').intVal()].noDeliverCostAbove);
            }            
            
            // we need to be sure
            this.store();
            if (updateView) {
                this.update_view();
            }
    
            return this;
        }
        else{  
            var order = null;
            try{
                order = $.Storage.get('order-' + this.service_id + '-' + YdMode);
            }
            catch ( err ){
                log('failed to get order from storage: ' + err);
            }
            if (order) {
                var decodedJson = $.base64.decode(order);
                if (decodedJson) {                
                    log('restore order from storage: ' + 'order-' + this.service_id + '-' + YdMode);
                    try{
                        order = JSON.parse(decodedJson);
                    }
                    catch ( err ){
                        log('failed to parse json from html5 storage: ' + err);
                        return this;
                    }
                    
                    if (order) {
                        if (order.version != this.version) {
                            log('version does not match, remove order from storage');
                            try{
                                $.Storage.remove('order-' + this.service_id);
                            }
                            catch ( err ){
                                log('failed to remove order from storage: ' + err);
                            }
                        }
                        else {
                            //extend all objects with its prototype to add the functions
                            $.extend(this, order, ydOrderPrototype);  
                            $.each(this.meals, function(k, v){
                                ydOrder.meals[k] = $.extend(v, ydMealPrototype); 
                            });

                            if (updateView) {
                                this.update_view();
                            }

                            //after all we validate written html against our object
                            if (!this.validate()) {
                            //what shall we do with the drunken sailor? 
                            }
                        }                    
                    }
                }
                else{
                    log('could not restore base64 string');
                }
            }
            else{
                log('could not find an order in html5 storage');
            }
        }
        
        this.update_view_amounts();
        return this;
    },
    
    /**
     * validate our order object against written html
     * this is needed if we load the html5 storage and the html
     * code has been written by php
     * @author mlaug
     * @since 26.07.2011
     */
    validate: function(){              
        var valid = true;
        
        //validate order cart
        valid = valid && $('.yd-full-amount').html() == this.calculate_amount(true);
        valid = valid && $('.yd-shopping-article').length == this.get_meal_count(true);
        
        //validate discount
        
        //validate anything else?
        
        log('validating html storage with written html, result: ' + valid);
        return valid;
    },
    
    /**
     * Call when finishing order
     * @author vpriem
     * @since 28.07.2011
     * @return ydOrder
     */
    finish: function(){
        if (this.service_id) {
            log('remove order from storage and clearing tracking cookie');
            $.cookie('yd-track',null);
            try{
                $.Storage.remove('order-' + this.service_id + '-' + YdMode);
            }
            catch ( err ){
                log('failed to remove order from storage: ' + err);
            }
        }
        else {
            log('no service id received, cannot remove order from storage')
        }
        
        return this;
    }
    
};

//add functions to ydOrder
$.extend(ydOrder, ydOrderPrototype);
