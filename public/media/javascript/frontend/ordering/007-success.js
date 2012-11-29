$(document).ready(function(){
   
   //this class is only on the success page
   //so it will trigger the deletion of the html5 storage
   if ( $('#yd-success-page').length > 0 ){  
       //do, whatever you want before, after that, we finish
       //and the order object will be deleted
       ydOrder.finish();
   }
   
});