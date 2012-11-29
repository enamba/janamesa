$(document).ready(function(){
    
    /**
     * @author vpriem
     * @since 28.06.2011
     */
    var voucher = false,
        netto = 0, 
        brutto = 0;
    $("select.yd-upselling-goods-count, :text.yd-upselling-goods-cost, #yd-upselling-goods-voucher").live('change',function(){
       
       var count = [];
       $("select.yd-upselling-goods-count").each(function(){
           count.push($(this).val());
       });
       
       var cost = [];
       $(":text.yd-upselling-goods-cost").each(function(){
           var reg = new RegExp("[^0-9]", "g");
           cost.push(parseInt(this.value.replace(reg, "")));
       });
       
       $("span.yd-upselling-goods-total").each(function(index){
           $(this).html(count[index] * cost[index] / 100);
       });
       
       $("span.yd-upselling-goods-unit").each(function(index){
           var unit = this.id.split("-")[4];
           var value = cost[index] / 100 / unit;
           value = '' + value;
           $(this).html(value.substr(0, 5));
       });
       
       netto = 0;
       for (var i = 0; i < count.length; i++) {
           netto += count[i] * cost[i];
       }
       brutto = netto + (netto * 0.19);
       
       $("#yd-upselling-goods-netto").html(netto / 100);
       $("#yd-upselling-goods-brutto").html(brutto / 100);
       
       $("#yd-upselling-goods-send-without, #yd-upselling-goods-send-with")
            .prop("disabled", true)
            .addClass("disabled");
            
       if (brutto > 0 && voucher !== false) {
           $("#yd-upselling-goods-send-without")
                .removeAttr("disabled")
                .removeClass("disabled");
           
           if (voucher >= brutto) {
               $("#yd-upselling-goods-send-with")
                    .removeAttr("disabled")
                    .removeClass("disabled");
           }
       }
    }).eq(0).change();
    
    /**
     * @author vpriem
     * @since 29.06.2011
     */
    $("#yd-upselling-restaurant").bautocomplete('/autocomplete/crm/type/service', function(item){
        if (item.id > 0) {
            voucher = false;
            $.ajax({
                url: "/administration_request_upselling_goods/voucher",
                type: "POST",
                data: {id: item.id},
                dataType: "json",
                success: function(json){
                    if (json.error) {
                        notification("error", json.error);
                    }
                    else {
                         voucher = json.voucher;
                         $("#yd-upselling-goods-voucher")
                            .html(voucher / 100)
                            .change();
                    }
                }
            });
        }
    });
    
    /**
     * @author vpriem
     * @since 13.07.2011
     */
    $(":text.yd-upselling-goods-cost").keyup(function(){
        $(this).change();
    });
    
     /**
     * @author vpriem
     * @since 14.07.2011
     */
    $('#yd-upselling-goods-grid .status').each(function(){
        var html = $(this).html();
        if (html == "Storno") {
            $(this).closest("tr").css('background', '#ffcece');
        }
        else if (html == "Bezahlt") {
            $(this).closest("tr").css('background', '#d5ffce');
        }
    });
});