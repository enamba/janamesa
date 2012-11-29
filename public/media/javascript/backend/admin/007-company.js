$(document).ready(function(){
   
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.10.2011
     */
    $('input[name=yd-company-edit-assoc-type]').live('click', function(){
        var serviceListMode = $('select[name=serviceListMode]').val();
        var companyId = $(this).attr('id').split('-')[5];
       
        log('edit company/restaurants assocs '+companyId + ' '+serviceListMode); 
        $.ajax({
            url: "/request_administration/companyrestaurantassoc",
            type: "POST",
            data: {
                companyId: companyId,
                serviceListMode: serviceListMode
            },
            dataType: "json",
            success: function(json){
                if (!json.result) {
                    notification("error", json.msg);
                    return false;
                }
                notification("info", json.msg);
                location.reload();
            }
        });
    });
   
});