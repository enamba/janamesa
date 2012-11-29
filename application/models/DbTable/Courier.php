<?php

/**
 * Courier DbTable
 * @author mlaug
 * @since 01.08.2010
 */
class Yourdelivery_Model_DbTable_Courier extends Default_Model_DbTable_Base {

    /**
     * Table name
     */
    protected $_name = "courier";

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Get deliver time
     * 
     * @param integer $cityId cityId of location to get delivertime for
     * @param integer $range  NOT USED ???
     * 
     * @return integer
     * 
     * @author mlaug
     * @since 01.08.2010, 10.08.2010 (vpriem)
     */
    public function getDeliverTime($cityId, $range = null) {

        //last, but not least, we get a typical plz range
        $result = $this->getAdapter()->fetchRow(
                "SELECT `deliverTime`
            FROM `courier_plz`
            WHERE `courierId` = ?
                AND `cityId` = ?
            LIMIT 1", array($this->getId(), $cityId)
        );
        if (!$result) {
            $result = $this->getAdapter()->fetchRow(
                    "SELECT `deliverTime`
                FROM `courier_plz` cp
                INNER JOIN `city` c ON cp.cityId = c.parentCityId
                WHERE cp.courierId = ?
                    AND c.id = ?
                LIMIT 1", array($this->getId(), $cityId)
            );
        }
        if ($result) {
            return $result['deliverTime'];
        }

        // 10 minutes by default
        return 10;
    }

    /**
     * Get deliver cost
     * 
     * @param integer $cityId cityId of location to get delivertime for
     * @param integer $range  NOT USED ???
     * 
     * @return integer
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 01.08.2010, 10.08.2010 (vpriem)
     */
    public function getDeliverCost($cityId, $range = null) {

        //last, but not least, we get a typical plz range
        $result = $this->getAdapter()->fetchRow(
                "SELECT `delcost`
            FROM `courier_plz`
            WHERE `courierId` = ?
                AND `cityId` = ?
            LIMIT 1", array($this->getId(), $cityId)
        );
        if (!$result) {
            $result = $this->getAdapter()->fetchRow(
                    "SELECT `delcost`
                FROM `courier_plz` cp
                INNER JOIN `city` c ON cp.cityId = c.parentCityId
                WHERE cp.courierId = ?
                    AND c.id = ?
                LIMIT 1", array($this->getId(), $cityId)
            );
        }
        if ($result) {
            return $result['delcost'];
        }

        //fuck!
        return 0;
    }

    /**
     * Get the list of all distinct fields
     * 
     * @return array
     * 
     * @author ????
     * @since ?????
     */
    public function getDistinctNameId() {

        return $this->getAdapter()
                        ->fetchAll(
                                "SELECT DISTINCT `id`, `name`
                FROM `courier`
                ORDER BY `id`");
    }

    /**
     * Find the restaurnat id this courier is working for
     * 
     * @return integer $restaurantId
     * 
     * @author ????
     * @since ????
     */
    public static function isCourierBy() {
        if (is_null($this->getId())) {
            return null;
        }

        $relTable = new Yourdelivery_Model_DbTable_Courier_Restaurant();
        return $relTable->isCourierBy($this->getId());
    }

    /**
     * get customized billing id
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ????
     * @return Zend_Db_Table_Row
     */
    public function getBillingCustomized() {

        return $this->getAdapter()->fetchRow(
            "SELECT `id`
            FROM `billing_customized`
            WHERE `refId` = ?
                AND `mode` = 'courier'", $this->getId());
    }

    /**
     * Get all plz locations of this courier
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.03.2011
     * @return array
     */
    public function getRanges() {

        return $this->getAdapter()->fetchAll(
            "SELECT cp.*
            FROM `courier_plz` cp
            INNER JOIN `city` c ON cp.cityId = c.id
            WHERE cp.courierId = ?", $this->getId());
    }

    /**
     * Get all associated restaurants
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.03.2011
     * @return array
     */
    public function getRestaurants() {
        
        return $this->getAdapter()->fetchAll(
            "SELECT `restaurantId`
            FROM `courier_restaurant`
            WHERE `courierId` = ?", $this->getId());
    }

    /**
     * delete delivering location
     * 
     * @param integer $id     id of row to delete
     * @param integer $cityId cityId
     * 
     * @return boolean
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 09.03.2011
     */
    public function deleteRange($id, $cityId = null) {

        if ($cityId !== null) {
            return (boolean) $this->getAdapter()->query(
                            "DELETE FROM `courier_plz`
                WHERE `cityId` = ?", $cityId);
        }
        return (boolean) $this->getAdapter()->query(
                        "DELETE FROM `courier_plz`
            WHERE `id` = ?", $id);
    }

    /**
     * get actual maximal customer number
     * 
     * @return integer
     * 
     * @author ????
     * @since ????
     */
    public static function getActualCustNr() {
        $db = Zend_Registry::get('dbAdapter');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try {
            $sql = sprintf('select max(customerNr) as max from courier');
            $result = $db->fetchRow($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 0;
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $result->max;
    }

}
