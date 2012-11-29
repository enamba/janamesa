<?php
/**
 * @author: Matthias Laug
 *
 * static class for session based messaging
 * @todo: provide constructor with sessio namespace
 *
 */

class Default_View_Notification {

    /**
     * our session variable
     * @var Zend_Session_Namespace
     */
    static $_session = null;

    static function setUpObject($type) {
        self::$_session = new Zend_Session_Namespace('Default');
        if(!isset(self::$_session->notification)) {
            self::$_session->notification = new stdclass;
        }

        if ( !isset(self::$_session->notification->$type) ){
            self::$_session->notification->$type = array();
        }
    }

    static function setNotification($type, $msg) {
        $msg = self::array2html($msg);

        self::setUpObject($type);

        if ( !in_array($msg,self::$_session->notification->$type) ){
            self::$_session->notification->{$type}[] = $msg;
        }
    }

    /**
     * display a error message for current user
     * @param mixed string|array $msg
     */
    static function error($msg){
        if ( is_array($msg) ){
            foreach($msg as $m){
                self::error($m);
            }
        }
        else{
            self::setNotification('error',$msg);
        }
    }

    /**
     * display a warning for current user
     * @param mixed string|array $msg
     */
    static function warn($msg){
        if ( is_array($msg) ){
            foreach($msg as $m){
                self::warn($m);
            }
        }
        else{
            self::setNotification('warn',$msg);
        }
    }

    /**
     * display a success message for current user
     * @param mixed string|array $msg
     */
    static function success($msg){
        if ( is_array($msg) ){
            foreach($msg as $m){
                self::success($m);
            }
        }
        else{
            self::setNotification('success',$msg);
        }
    }

    /**
     * display a success message for current user
     * @param mixed string|array $msg
     */
    static function info($msg){
        self::success($msg);
    }

    /**
     * get $data and flatten array if present
     * append and prepend <li> tags
     * @param mixed string|array $data
     * @return string
     */
    static function array2html($data){

        //if element is not an array we just append and prepend
        //<li> tags and return
        if ( !is_array($data) ){
            return $data;
        }

        //start our final html output
        $html = "";
        
        foreach($data as $elem){
            if ( empty($elem) ){
                continue;
            }
            
            //for each array layer we call ourself
            $html = $html . " " . self::array2html($elem) . ".";

        }

        $html = str_replace("..", ".", $html);
        
        //return output
        return $html;

    }

}
