/**
 * @author vpriem
 * @since 19.07.2011
 */
function YdSize(id, name, cost){
    return {
        id: id,
        name: name,
        cost: parseInt(cost)
    };
}

/**
 * @author vpriem
 * @since 19.07.2011
 */
function YdExtra(id, name, cost, count){
    return {
        id: id,
        name: name,
        cost: parseInt(cost),
        count: parseInt(count)
    };
}

/**
 * @author vpriem
 * @since 19.07.2011
 */
function YdOption(id, name, cost){
    return {
        id: id,
        name: name,
        cost: parseInt(cost),
        type: 'option'
    };
}

/**
 * @author jens naie
 * @since 24.08.2012
 */
function YdMealOption(id, name, cost){
    return {
        id: id,
        name: name,
        cost: parseInt(cost),
        type: 'mealoption'
    };
}

/**
 * @author vpriem
 * @since 19.07.2011
 * @hint put no functions in here, but in ydOrderPrototype
 * this object will be stored in html5 storage and will not store
 * any functions. Those will be extended onload
 */
var ydMealPrototype = {
    addExtra: function(extra){
        this.extras.push(extra);
    },
        
    addOption: function(option){
        this.options.push(option);
    },

    getCost: function(format){
        var total = this.size.cost;

        if (format === true) {
            return int2price(total, true);
        }

        return total;
    },
    
    getAllCost: function(format, single){
        var total = this.size.cost;

        $.each(this.extras, function(k, v){
            total += v.cost * v.count;
        });

        $.each(this.options, function(k, v){
            total += v.cost;
        });

        if (!single) {
            total = total * this.count;
        }

        if (format === true) {
            return int2price(total, true);
        }

        return total;
    },
    
    /**
     * Get HTML representation of this meal
     * @author vpriem
     * @since 28.11.2011
     * @param string hash
     * @return string
     */
    getHtml: function(hash){
        var html = '\
            <li class="yd-shopping-input yd-meal-' + hash + '" id="yd-meal-count-li-' + hash + '">\
                <input id="yd-meal-count-' + hash + '" type="text" name="meal[' + hash + '][count]" value="' + this.count + '" class="yd-only-nr yd-change-item-count" maxlength="3" />\
                <input class="yd-meal-' + hash + '" type="hidden" name="meal[' + hash + '][special]" value="' + this.special + '" />\
                <input class="yd-meal-' + hash + '" type="hidden" name="meal[' + hash + '][size]" value="' + this.size.id + '" />\
                <input class="yd-meal-' + this.id + '" type="hidden" name="meal[' + hash + '][id]" value="' + this.id + '" />\
            </li>\
            <li id="yd-meal-' + hash + '" class="yd-shopping-article yd-meal-' + hash + '">' + this.name +  " " + (this.exMinCost ? "<span class='yd-shopping-notmincost'> " + $.lang("exmincost") + "</span>" : "") + '</li>\
            <li class="yd-shopping-options yd-meal-' + hash + '">\
                <a class="yd-option-plus increase-item" id="yd-increase-item-' + hash + '"></a>\
                <a class="yd-option-minus decrease-item" id="yd-decrease-item-' + hash + '"></a>\
                <a class="yd-option-trash delete-item" id="yd-delete-item-' + hash + '"></a>\
            </li>\
            <li class="yd-shopping-extra yd-meal-' + hash + '">\
            <span class="yd-clearfix"><em>' + $.lang("size", this.size.name) + '</em><span class="yd-shopping-singular-price"> ' + $.lang("every", int2price(this.getCost(false))) + '</span></span>';

        var count = this.count;
        
        // append options
        $.each(this.options, function(k, v){
            html += '<input class="yd-meal-' + hash + '" type="hidden" name="meal[' + hash + '][' + v.type + 's][]" value="' + v.id + '" />\n\
                     <span class="yd-clearfix"><em>' + v.name + '</em>';
            if (v.cost > 0 || v.type == "mealoption") {
                html += '+&nbsp;' + int2price(v.cost * count, true);
            }
            html += '</span>';
        });

        // append extras
        $.each(this.extras, function(k, v){
            html += '<input class="yd-meal-' + hash + '" type="hidden" name="meal[' + hash + '][extras]['+v.id+'][id]" value="' + v.id + '" />\n\
                    <input class="yd-meal-' + hash + '" type="hidden" name="meal[' + hash + '][extras]['+v.id+'][count]" value="' + v.count + '" />\n\
                    <span class="yd-clearfix"><em>' + v.name + ' ' + v.count + 'x</em>';
            if (v.cost > 0) {
                html += '+&nbsp;' + int2price(v.cost * v.count * count, true);
            }
            html += '</span>';
        });

        html += '</li><li class="yd-shopping-price yd-meal-' + hash + '">' + this.getAllCost(true) + '</li>';
        
        return html;
    }
    
};

function YdMeal(){
    return $.extend({
        id: null,
        name: "",

        // how much is the fish?
        count: 1,

        // any "extra wurst?"
        special: "",

        // how much of this meal do we have to add
        minCount: 1,
        
        // is this meal from the minimum cost excluded
        exMinCost: true,
        
        size: {},
        extras: [],
        options: []
    }, ydMealPrototype);
}