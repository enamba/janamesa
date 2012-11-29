/* 
 * Set telephone mask for telephone input fields.
 * @author jnaie
 */

$(document).ready(function(){
    var masklength;
    $("#telefon, #tel").each(function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length == 8) {
            this.value = '119' + this.value;
        } else if (this.value.length == 9) {
            this.value = '11' + this.value;
        }
        var match = this.value.match(/^(11)(\d{8})$/);
        if(match) {
            this.value = match[1] + "9" + match[2];
        }
        if (this.value.indexOf('11')===0) {
            $(this).mask("(99) 99999-9999");
            masklength = 9;
        } else {
            $(this).mask("(99) 9999-9999");
            masklength = 8;
        }
    });
    $("#telefon, #tel").bind("keypress.mask", function(o){
        var caret = $(this).caret();
        if(o.originalEvent && o.originalEvent.keyCode != 8 && caret.begin == 5) {
            if (masklength == 8 && this.value.indexOf('(11)') != -1) {
                $(this).mask("(11) 99999-9999");
                this.value = '(11) _____-____';
                masklength = 9;
                $(this).caret(5);
            } else if (masklength == 9 && this.value.indexOf('(11)') == -1 && this.value.search(/^\(\d\d\)/) != -1) {
                var cityCode = this.value.substr(0,5);
                $(this).mask(cityCode + "9999-9999");
                this.value = cityCode + '____-____';
                masklength = 8;
                $(this).caret(5);
            }
        }
        return true;
    });
});
