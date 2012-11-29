<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author mlaug
 */
class Default_Helpers_File {
    
    public static function ShowFileName($filename)
    {
        return trim(preg_replace( '/^.+[\\\\\\/]/', '', $filename ));
    }
    
    /**
     * get file extension of given file
     * @author mlaug
     * @since 04.02.2011
     * @param string $filename
     * @return string
     */
    public static function getFileExtension($filename){
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
    
}
