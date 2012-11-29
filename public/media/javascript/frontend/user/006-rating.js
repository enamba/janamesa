$(document).ready(function(){
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.02.2012
     */
    if ($('#yd-rate-form').length) {
        //check if deleted
        if ($('#yd-rating-deleted').length) {
            openDialog("/request_user/ratingdeleted", {
                modal :true, 
                width: 400,
                height: 380,
                close: function(e, ui) {
                    $(ui).dialog('destroy');
                },
                beforeClose: function (event, ui) {
                    if($('.yd-profile-not-logged-in').length > 0) {
                        window.location.href = "/";
                    } else {
                        window.location.href = "/user/ratings";
                    }
                    return false;
                }
            });
        } else {
            $('a.yd-rate-advise').live('click', function(){        
                $('a.yd-rate-advise').removeClass('active');
          
                $(this).addClass('active');
                var id = this.id.split('-')[2];         
                if(id == 'yes') {
                    $('#advise').prop('value', 1);
                }else {
                    $('#advise').prop('value', 0);
                }
          
            });
            //quality
            $('img.rate-1').hover(function(){
                var q = this.id.split('-')[3];
                $('img.rate-1').each(function(){
                    if (this.id.split('-')[3] <= q) {
                        this.src = '/media/images/yd-profile/star-big-hover.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-big-empty.png';
                    }
                });
      
                return false;
            }, function() {
                var q = $('#rate1').val();         
                $('img.rate-1').each(function(){
                    if (this.id.split('-')[3] <= q) {
                        this.src = '/media/images/yd-profile/star-big-full.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-big-empty.png';
                    }
                });
                $('#rate1').prop('value',q);
                return false;
            });
        
            $('img.rate-1').live('click', function(){
                var q = this.id.split('-')[3];
          
                $('#rate1').prop('value',q);
                return false;
            });
        
            //delivery
        
            $('img.rate-2').hover(function(){
                var q = this.id.split('-')[3];
                $('img.rate-2').each(function(){
                    if (this.id.split('-')[3] <= q) {
                        this.src = '/media/images/yd-profile/star-big-hover.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-big-empty.png';
                    }
                });
      
                return false;
            }, function() {
                var q = $('#rate2').val();    
                $('img.rate-2').each(function(){
                    if (this.id.split('-')[3] <= q) {
                        this.src = '/media/images/yd-profile/star-big-full.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-big-empty.png';
                    }
                });
                $('#rate2').prop('value',q);
                return false;
            });
        
            $('img.rate-2').live('click', function(){
                var q = this.id.split('-')[3];
          
                $('#rate2').prop('value',q);
                return false;
            });
        
            //meals  
            $('img.yd-rate-meal').hover(function(){
                var mealId = this.id.split('-')[2];
                var star =  this.id.split('-')[3];
                $('img.rate-meal-id-' + mealId).each(function(){
                    if (this.id.split('-')[3] <= star ) {
                        this.src = '/media/images/yd-profile/star-small-hover.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-small-empty.png';
                    }
                });
            },
            function(){
                var mealId = this.id.split('-')[2];
                var star = $('#meal-rate-'+mealId).val();
                $('img.rate-meal-id-' + mealId).each(function(){
                    if (this.id.split('-')[3] <= star ) {
                        this.src = '/media/images/yd-profile/star-small-full.png';
                    }
                    else {
                        this.src = '/media/images/yd-profile/star-small-empty.png';
                    }
                });
            });
      
            $('img.yd-rate-meal').live('click', function(){
                var mealId = this.id.split('-')[2];
                var star =  this.id.split('-')[3];

                $('#meal-rate-'+mealId).prop('value',star);
         
            });
        
        }
                        
    }

});