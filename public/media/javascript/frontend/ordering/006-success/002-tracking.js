/**
 *  Build tracking Pixel with Url
 *  call in header
 *  @author Daniel Hahn <hahn@lieferando.de> 
 *  @since 25.10.2011
 */
function trackSuccess(url) {
    $(document).ready(function(){
        var img = document.createElement("img");
        img.src = url;
        img.width = 1;
        img.height = 1;
        $('body').append(img);
    });
}