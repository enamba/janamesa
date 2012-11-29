/**
 * @author vpriem
 * @since 08.07.2011
 */
if (CURRENCY === undefined) {
    var CURRENCY = "€";
}
function int2price(price, currency, decimals, dec_point, thousands_sep) {
    
    if (currency === undefined) {
        currency = false;
    }
    
    if (decimals === undefined) {
        decimals = 2;
    }
    
    if (dec_point === undefined) {
        dec_point = ',';
    }
    
    if (thousands_sep === undefined) {
        thousands_sep = '.';
    }
    
    var d = Math.pow(10, decimals);
    price = '' + Math.round((price / 100) * d) / d;
    
    var p = price.split('.');
    if (p[0].length > 3) {
        var pos = p[0].length - 3;
        p[0] = p[0].substring(0, pos) + thousands_sep + p[0].substring(pos);
    }
    if (p.length < 2) {
        p.push('0');
    }
    if (p[1].length < decimals) {
        p[1] += new Array(decimals - p[1].length + 1).join('0');
    }
    if (!decimals) {
        p = p.splice(0, 1);
    }
    return p.join(dec_point) + (currency ? '&nbsp;' + CURRENCY : '');
}