//budget object

var ydBudget = {
    
    _budget : {},
    
    get_budget : function(){
        return this._budget;
    },
    
    //add a validated budget
    add : function(email,amount,code,addition,addition2){
        
        //precheck
        if ( email === undefined || email == ''){
            log('no email provided');
            return false;
        }
        
        amount = parseInt(amount);
        if ( amount === undefined || isNaN(amount) || amount <= 0){
            log('no amount provided');
            return false;
        }
        
        if ( code === undefined ){
            log('undefined code, reseting to empty string');
            code = '';
        }
        
        if ( addition === undefined ){
            log('undefined addition, reseting to empty string');
            addition = '';
        }
        
        if ( addition2 === undefined ){
            log('undefined addition2, reseting to empty string');
            addition2 = '';
        }
        
        var hash = sha1(email);
        log('adding budget from ' + email);
        this._budget[hash] = {         
            email : email,
            amount : amount,
            code : code,
            addition : addition
        };
        
        //reset values
        $('#check_email').val("");
        $('#projectcode').val("");
        $('#check_amount_cent').val("");
        $('#check_amount_euro').val("");
        
        this.update_view();
        return hash;
    },
    
    //remove a budget
    remove : function(hash){
        var budgets = {};
        $.each(this._budget, function(k,v){
            if ( k != hash ){
                budgets[k] = v;
            }
            else{
                log('found ' + k + ' and remove it from budget');
            }
        });
        this._budget = budgets;
        this.update_view();
    },
    
    //get overall amount of all budgets
    get_amount : function(format){
        var amount = 0;
        $.each(this._budget, function(k,v){
            amount += parseInt(v.amount);
        });
        var form = $('#finishForm')[0];
        if ( form && form.own_budget){
            var own_budget = parseInt(form.own_budget.value);
            amount += own_budget;
        }
        else{
            amount = 0;
        }
        if ( format ){
            amount = int2price(amount,true);
        }
        return amount;
    },
    
    update_view : function(){
        $('#yd-invite-budget').html('');
        $('#budtable').html('');
        if ( !$.isEmptyObject(this._budget) ){
            log('found some budgets, will update view');
            $('#yd-invite-budget').show();
            $('#budtable').show();
            $.each(this._budget, function(k,v){
                if ( v ){
                    $('#yd-invite-budget').append('<li><em><a href="#" class="mail-erase yd-budget-erase" id="yd-budet-erase-'+ k +'-2"></a>' + v.email + '</em><span><b>' + int2price(v.amount,true) + '</b></span></li>');
                    $('#budtable').append('<span><a class="mail-erase yd-budget-erase cursor" id="yd-budget-erase-' + k + '-1"></a>Budget von '+ v.email +': <b>' + int2price(v.amount,true) + '</b></span>');
                    $('#budtable').append('<input type="hidden" name="budget['+k+'][email]" value="'+ v.email +'" />');
                    $('#budtable').append('<input type="hidden" name="budget['+k+'][amount]" value="'+ v.amount +'" />');
                    $('#budtable').append('<input type="hidden" name="budget['+k+'][code]" value="'+ v.code +'" />');
                    $('#budtable').append('<input type="hidden" name="budget['+k+'][addition]" value="'+ v.addition +'" />');
                }
            });
        }
        else{
            log('no budgets found, erase and hide view');
            $('#yd-invite-budget').hide();
            $('#budtable').hide();
        }
        
        ydOrder.update_view_amounts();
        
    }
    
};