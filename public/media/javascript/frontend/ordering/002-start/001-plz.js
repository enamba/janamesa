$(document).ready(function(){
    
    /**
     * Plz remote autocompletion
     * @author vpriem
     * @since 17.03.2011
     */
    $('.yd-plz-autocomplete').plzAutocomplete();

    $('#yd-plz-search, #yd-plz-search-big').each(function(){
        var $this = $(this);
        
        // TODO: use yd-plz-autocomplete-autosubmit class
        $this.plzAutocomplete(true);
        
        if ($this.is(":visible")) {
            $this.focus()
                 .caret(0);
        }
    });
     
    /**
     * if we need a cityId in this form, we add this class and make sure
     * that the customer is informed inline
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.02.2012
     */
    $('.yd-form-plz-required').live('blur', function(){
        var form = this.form;
        var $input = $(this);
        if ($input.val().length > 0 && $('input[name="cityId"]', form).val().length == 0) {  
            $input.removeClass('yd-form-valid')
                  .addClass('yd-form-invalid');         
        }
    });

});