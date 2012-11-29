<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 18.11.2010
 */
class Yourdelivery_Model_DbTable_Tracking_Pixel extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'tracking_pixel';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';


    /**
     * find row by given email and campaign
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2010
     * @param string $email
     * @param string $campaign
     * @return Zend_DbTable_Row_Set
     */
    public static function findByEmailAndCampaign($email, $campaign){

        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("p" => "tracking_pixel") )
                    ->where("p.address = '".$email."' AND p.campaign = '".$campaign."'");

        return $db->fetchRow($query);
    }


    /**
     * increase count of called image
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.11.2010
     * @param integer $id
     * @return void
     */
    public function increaseCount($id){

        if( is_null($id) ){
            return null;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->query('UPDATE tracking_pixel set count = count+1 WHERE id='.$id);
        
    }

}
