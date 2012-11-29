/**
* Facebook connect
* @author Jens Naie <naie@lieferando.de>
* @since 10.07.2012
*/
if(typeof(FbAppId) != "undefined") {
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
    $(document).ready(function(){
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/"+LOCALE+"/all.js";
            fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
    });
}