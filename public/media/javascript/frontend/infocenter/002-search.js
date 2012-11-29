$(document).ready(function(){
   
   /**
    * @author vpriem
    * @since 23.01.2012
    */
    $('#yd-infocenter-search-trigger').click(function() {
        $('#yd-infocenter-search').trigger("keyup", [13]);
        this.blur();
        return false;
    });
    
    if ($('#yd-infocenter-search').length) {
        $('#yd-infocenter-search').val("").quicksearch('.accordion li', {
            'selector': "p",
            'bind': "enter",
            'show': function() {
                var val = $('#yd-infocenter-search').val();
                if (!val.length) {
                    return;
                }

                $("a", this).addClass('active');
                $("p", this).addClass("yd-found-sth").show();
            },
            'hide': function() {
                var val = $('#yd-infocenter-search').val();
                if (!val.length) {
                    return;
                }

                $("a", this).removeClass('active');
                $("p", this).removeClass("yd-found-sth").hide();
            },
            onBefore: function() { 
                $("p.yd-found-sth").unhighlight();
            },
            onAfter: function() { 
                var val = $('#yd-infocenter-search').val();
                if (!val.length) {
                    return;
                }

                if (!$("p.yd-found-sth").length) {
                    notification("error", $.lang("infocenter-no-match"));
                    return;
                }

                var vals = val.toLowerCase().split(' ');
                for (var i = 0, n = vals.length; i < n; i++) {
                    $("p.yd-found-sth").highlight(vals[i]);
                }
                
                var results = $("span.highlight").length;
                notification("success", $.nlang("infocenter-found", "infocenter-found-n", results, results));
            }
        }).emptytext();
    }
    
});