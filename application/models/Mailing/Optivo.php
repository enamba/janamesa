<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Optivo
 *
 * @author daniel
 */
class Yourdelivery_Model_Mailing_Optivo extends Default_Model_Base {

    protected $_table = null;
    protected $_citys = null;

    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Mailing_Optivo();
        }
        return $this->_table;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012
     * @param type $cityIds
     * @return boolean
     */
    public function setCitys($cityIds) {

        $currentIds = array();

        $citys = $this->getCitys();
        //delete
        foreach ($citys as $city) {
            if (!in_array($city->getId(), $cityIds)) {
                Yourdelivery_Model_Mailing_Optivo_City::deleteByMailingAndCityId($this->getId(), $city->getId());
            } else {
                $currentIds[] = $city->getId();
            }
        }

        //add
        foreach ($cityIds as $cityId) {
            if (!in_array($cityId, $currentIds)) {
                $rr = new Yourdelivery_Model_Mailing_Optivo_City();
                $rr->setMailingId($this->getId());
                $rr->setCityId($cityId);
                $rr->save();
            }
        }

        $this->_citys = null;

        return true;
    }

    /**
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012
     * @return type
     */
    public function getCitys() {
        if (is_null($this->_citys)) {
            $tmp = array();
            $relations = $this->getTable()->getCurrent()->findDependentRowset("Yourdelivery_Model_DbTable_Mailing_Optivo_City");

            foreach ($relations as $relation) {
                $tmp[] = new Yourdelivery_Model_City($relation->cityId);
            }

            if (count($tmp) > 0) {
                $this->_citys = $tmp;
            }
        }
        return $this->_citys;
    }

    /**
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012
     * @return type
     */
    public function getOrderCountAsArray() {

        $orderCountString = $this->_data['customerOrderCount'];

        $tmpArr = explode(";", $orderCountString);


        return array_filter($tmpArr, function($var) {
                            return (is_numeric($var));
                        }
        );
    }
    
    
    /**
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012
     * @return type
     */
    public function hasParameter($parameter) {
        
        $paramters = $this->_data['parameters'];
                        
        $params = explode(";", $paramters);       
        
        if(in_array($parameter, $params)) {
            return true;
        }
        
        return false;        
    }

}

?>
