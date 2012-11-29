$(document).ready(function(){
    /**
     * expand cross selling box for premium orders
     * @author mlaug
     * @since 05.01.2010
     */
    if ($("#yd-expand-premium-head").length) {
        $("#yd-expand-premium-body").hide();
        $("#yd-expand-premium-minus").hide();
        $("#yd-expand-premium-head").click(function(){
            $("#yd-expand-premium-minus, #yd-expand-premium-plus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

    /**
     * expand cross selling box for catering orders
     * @author mlaug
     * @since 05.01.2010
     */
    if ($("#yd-expand-cater-head").length) {
        $("#yd-expand-cater-body").hide();
        $("#yd-expand-cater-head-minus").hide();
        $("#yd-expand-cater-head").click(function(){
            $("#yd-expand-cater-head-minus, #yd-expand-cater-head-plus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

    /**
     * expand cross selling box for great orders
     * @author mlaug
     * @since 05.01.2010
     */
    if ($("#yd-expand-great-head").length) {
        $("#yd-expand-great-body").hide();
        $("#yd-expand-great-head-minus").hide();
        $("#yd-expand-great-head").click(function(){
            $("#yd-expand-great-head-minus, #yd-expand-great-head-plus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

    /**
     * expand cross selling box for fruit orders
     * @author mlaug
     * @since 05.01.2010
     */
    if ($("#yd-expand-fruit-head").length) {
        $("#yd-expand-fruit-body").hide();
        $("#yd-expand-fruit-head-minus").hide();
        $("#yd-expand-fruit-head").click(function(){
            $("#yd-expand-fruit-head-minus, #yd-expand-fruit-head-plus").toggle();
            $(this).next().slideToggle("fast");
            return false;
        });
    }

});