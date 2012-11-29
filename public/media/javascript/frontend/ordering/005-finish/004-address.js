$(document).ready(function(){
    
    //if customers wishes to change deliver cost on finish page, update it
    $('#changeCityId').live('change',function(){
        var range = ydCurrentRanges[this.value];
        if ( range === 'undefined' ){
            log('undefined deliver range, weird!');
        }
        ydOrder.set_deliver_cost(range.deliverCost, range.noDeliverCostAbove);
        $('input[name="cityId"]').val(this.value);
        log('updating deliver cost to ' + range.deliverCost);
    });
   
    // if we have an private state we may prefill the address field
    $('#finishForm').each(function(){
        
        if (ydState.getKind() != 'priv') {
            return false;
        }
        
        if ( ydRecurring._lastorder !== null){
            
            $.ajax({
                url: '/request_order/lastorder',
                data: {
                    type: 'json',              
                    hash: ydRecurring._lastorder,
                    mode: ydState.getMode(),
                    kind: ydState.getKind()
                },
                dataType: 'json',
                success: function (json, state, code) {
                    if (code.status == 200) {
                        
                        
                        log('found an old order, filling out forms');
                    
                        var form = $('#finishForm')[0];
                    
                        // only prefill prename and name
                        if (!ydState.maybeLoggedIn()) {
                            if (form.prename.value === '') {
                                form.prename.value = json.prename || "";
                            }
                            if (form.name.value === '') {
                                form.name.value = json.name || "";
                            }
                        }
                        
                        if (form.telefon.value === '') {
                            form.telefon.value = json.tel || "";
                        }
                        
                        if (form.email.value === '') {
                            form.email.value = json.email || "";
                        }
                        
                        if (form.digicode) {
                            if (form.digicode.value === ''){
                                form.digicode.value = form.comment.value.split("\nDigicode: ")[1] || "";
                                form.comment.value = form.comment.value.replace(/Digicode.*/, '');
                            }
                        }
                        
                        if ( json.cityId != ydState.getCity() ){
                            log('found an old order, but cityIds are not matching, only filling out non address related content');
                            return false;
                        }

                        // fill out address if any values are not yet filled
                        if (form.street.value === '') {
                            form.street.value = json.street || "";
                        }
                        if (form.hausnr.value === '') {
                            form.hausnr.value = json.number || "";
                        }
                        if (form.company.value === '') {
                            form.company.value = json.company || "";
                        }
                        if (form.etage.value === '') {
                            form.etage.value = json.etage || "";
                        }
                        if (form.comment.value === '') {
                            form.comment.value = json.comment || "";
                        }
                    
                        // finally check again the form and lighten them up green :)                   
                        $(':input.yd-form-invalid').each(function(){
                            if (this.value.length) {
                                $(this)
                                .removeClass('yd-form-invalid')
                                .addClass('yd-form-valid');
                            }
                        });
                    }
                    else{
                        log('found no old order, will let the customer fill in for itself');
                    }
                }
            });
        }
        
        return false;
        
    });
   
});