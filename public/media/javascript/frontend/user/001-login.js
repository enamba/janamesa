$(document).ready(function(){

    /**
     * @author vpriem
     * @since 09.02.2011
     */
    $('#yd-login-toggle-in').live('click', function(){
        $('#yd-login-toggle-out').fadeToggle();
        return false;
    });

    /**
     * @author vpriem
     * @since 23.08.2011
     */
    $('a.yd-log-out').live('click', function(){
        ydCustomer.init();
        ydState.init();
    });
    
    /**
     * @author mlaug
     */
    $('#login').attr('action', $('#login').attr('action') + '?redirect_url=' + encodeURI(document.location.href));
    $('#yd-logout').attr('href', $('#yd-logout').attr('href') + '?redirect_url=' + encodeURI(document.location.href));
        
    /**
     * alter all links if customer is logged in
     * @author mlaug
     */
    if ( ydState.maybeLoggedIn() ){
        $('a').each(function(){
            if ( this.href == 'http://'+document.location.hostname+'/' ){
                if ( ydState.getKind() == 'comp' ){
                    log('changing link / => /order_company/start, since the customer is logged in');
                    this.href = '/order_company/start';
                }
                else{
                    log('changing link / => /order_private/start, since the customer is logged in');
                    this.href = '/order_private/start';
                }
            }
        });
    }
});