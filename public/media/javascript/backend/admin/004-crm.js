$(document).ready(function(){
    
    $(".yd-crm-staff-autocomplete").live("click", function(){
        $(this).select();
    });
    
    $(".yd-crm-staff-autocomplete").live("blur", function(){
        if ( $(this).val() == '') {
            $(this).val($('#assignedToValueOld').val());
            $('#assignedToId').val($('#assignedToIdOld').val());
        }
    });

    // show ticket nr input when corresponding checkbox is selected
    $("#yd-crm-bind-ticket-checkbox").live("click", function(){
        if ( $(this).is(':checked') ){
            $('#yd-ticket-nr').show();
        }
        else{
            $('#yd-ticket-nr').hide();
        }
    });

    // hide reasign input if ticket will be closed and show if not
    $('#yd-crm-close-checkbox').live('change',function(){
        if ( $(this).is(':checked') ){
            $('#yd-crm-reassign').hide();
        }
        else{
            $('#yd-crm-reassign').show();
        }
    });
    
    // if correct name was entered, uncheck "close ticket" box
    // just for constistency, because this input field will be hidden when the box is checked
    $('#yd-crm-reassign').live('blur',function(){
        if ($('#yd-crm-assigned').val() != '') {
            $('#yd-crm-close-checkbox').attr('checked', false);
        }
        
    });

    // uncheck email when telephone was checked
    $('#yd-crm-tel').change(function(){
        if ( $(this).is(':checked') ){
            $('#yd-crm-email').attr('checked', false);
        }
    });

    // uncheck telephone when email was checked
    $('#yd-crm-email').change(function(){
        if ( $(this).is(':checked') ){
            $('#yd-crm-tel').attr('checked', false);
        }
    });
    
    // show all crm reasons when department was selected
    $('#yd-crm-department').change(function(){
        var dept = $(this).val();

        $.post("/request_administration/getcrmreasons", {
            department : dept
        },
        function(reasons) {
            if (reasons.length > 0) {
                $('#yd-crm-reason-id').html(reasons);
            }
        }
        );
    });    
    
    
    /**
     * autocomplete for crm reference 
     * @author alex
     * @since 21.06.2011
     */    
    $('#yd-crm-service-autocomplete-1').bautocomplete('/autocomplete/crm/type/service');
    $('#yd-crm-company-autocomplete-1').bautocomplete('/autocomplete/crm/type/company');
    $('#yd-crm-user-autocomplete-1').bautocomplete('/autocomplete/crm/type/user');
    $('#yd-crm-service-autocomplete-2').bautocomplete('/autocomplete/crm/type/service');
    $('#yd-crm-company-autocomplete-2').bautocomplete('/autocomplete/crm/type/company');
    $('#yd-crm-user-autocomplete-2').bautocomplete('/autocomplete/crm/type/user');
    
    $('.yd-crm-staff-autocomplete').bautocomplete('/autocomplete/crm/type/staff');
    $('#yd-crm-staff-autocomplete-1').bautocomplete('/autocomplete/crm/type/staff');
    $('#yd-crm-staff-autocomplete-2').bautocomplete('/autocomplete/crm/type/staff');
    
});