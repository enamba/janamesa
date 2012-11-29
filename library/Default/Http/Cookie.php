<?php


class Default_Http_Cookie{
       
    public function __construct($name = null){

        if ( !is_null($name) ){
            if ( array_key_exists($name, $_COOKIE) ){
                //wrap cookie in class
            }
            else{
                throw new Exception('Cookie could not be found');
            }
        }

    }

    public function addToResponse(){
        
    }

}

?>
