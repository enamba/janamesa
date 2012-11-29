/**
 * Cookie detection
 * @author vpriem
 * @since 10.02.2011
 */
function isCookieEnabled(){
    var cookieEnabled;
    if (typeof navigator.cookieEnabled === "undefined"){
        document.cookie = "testcookie=qwertz; expires=0; path=/";
        cookieEnabled = document.cookie.indexOf("testcookie") < 0;
    }
    else {
        cookieEnabled = navigator.cookieEnabled;
    }
    return cookieEnabled;
}