$(document).ready(function(){
    // expand budget box sidebar
    if ($("#yd-expand-budget-head").length > 0) {
        $("#yd-expand-budget-body").hide();
        $("#yd-expand-budget-head-minus").hide();
        $("#yd-expand-budget-head").click(function() {
            $("#yd-expand-budget-head-minus, #yd-expand-budget-head-plus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }
});