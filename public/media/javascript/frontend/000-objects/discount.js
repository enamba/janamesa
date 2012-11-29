function YdDiscount(){
    return {
        discount : null,
        kind : 0,
        value : 0,
        min_amount : 0,
        cart_total : 0,
        get_amount : function(format){
            
            if ( this.kind == 1 ){
                return this.value;
            }
            else{
                if ( this.value == 100 ){
                    return this.cart_total;
                }
                else{
                    var perc = this.cart_total/100*this.value;
                    return perc;
                }
            }
            
        }
    };
}

