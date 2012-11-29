$(document).ready(function(){
    
    /**
     * @author vpriem
     */
    $("#yd-cms-content div.yd-cms-box-body").each(function(){
        var i = 0, prev;
        $("*", this).each(function(){
            if (this.tagName.substr(0, 1).toUpperCase() == "H") {
                i = 0;
            }
            else if (this.tagName.toUpperCase() == "P") {
                i++;
                if (i > 1) {
                    if (i == 2) {
                        $('<a href="#">&nbsp;Mehr&nbsp;&raquo;</a>')
                        .click(function(){
                            $(this).closest("p").nextUntil(":visible").show();
                            $(this).remove();
                            return false;
                        })
                        .appendTo(prev);
                    }
                    $(this).hide();
                }
                prev = this;
            }
        });
    });

    /**
     * @author vpriem
     */
    $("#yd-cms-sidebar ul").each(function(){
        var i = 0, ul = this;
        $("li", this).each(function(){
            if (i > 5) {
                $(this).hide();
            }
            i++;
        });
        if (i > 5) {
            $('<a href="#">&nbsp;Mehr&nbsp;&raquo;</a>')
            .click(function(){
                $("li:hidden", ul).show();
                $(this).remove();
                return false;
            })
            .appendTo(this)
            .wrap('<li></li>');
        }
    });

});