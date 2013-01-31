/**
* Facebook connect
* @author Jens Naie <naie@lieferando.de>
* @since 10.07.2012
*/
if(typeof(FbAppId) != "undefined") {
//    $(document).ready(function(){

     
        //initialize fbAsyncInit - will be replaced in 001-base.js after checking connection
        window.fbAsyncInit = function() {
            fbAsyncInitConnect();
        }
        window.fbAsyncInitConnect = function() {
            FB.init({
                appId: FbAppId,
                status: true,
                cookie: true,
                xfbml: true
            });
            //add event to like action
            FB.Event.subscribe('edge.create',
                function(response) {
                    $.ajax({
                        type : 'POST',
                        url : '/request_user_fidelity/add',
                        data : {
                            action : 'facebookfan',
                            validate : '__**'
                        }
                    });
                }
                );
        };
//    });
}
