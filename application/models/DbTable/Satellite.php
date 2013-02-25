<?php

/**
 * Satellite Db Table
 * @author Vincent Priem <priem@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Satellite extends Default_Model_DbTable_Base {

    /**
     * Table name
     */
    protected $_name = 'satellites';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Find row by domain
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @param string $domain
     * @return Zend_Db_Table_Row_Abstract
     * @modified 21.11.2011 mlaug
     */
    public function findByDomain($domain) {
        //check for url domains
        $select = $this->select()
                ->where("`domain` = ?", $domain)
                ->where("`disabled` = 0");
        return $this->fetchRow($select);
    }

    /**
     * Find group of domains, which inherit services under
     * a substructure like /shop/123 or sth like that
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     * @param type $domain
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllByDomain($domain, $limit = null) {
        //check for url domains
        $select = $this->select()
                ->where("`domain` = ?", $domain)
                ->where("`disabled` = 0");
        
        if ( $limit > 0 ){
            $select->limit($limit);
        }
        
        return $this->fetchAll($select);
    }
    
    /**
     * Find row by restaurantId
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @param int $restaurantId
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findByRestaurantId($restaurantId) {

        return $this->fetchRow(
                        $this->select()
                                ->where("`restaurantId` = ?", $restaurantId)
        );
    }

    /**
     * Get pictures
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return array
     */
    public function getPictures() {

        $db = $this->getAdapter();
        return $db->fetchAll(
                        "SELECT *
            FROM `satellite_pictures`
            WHERE `satelliteId` = ?", $this->getId()
        );
    }

    /**
     * Get css properties
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @return array
     */
    public function getCssProperties() {

        $db = $this->getAdapter();
        $rows = $db->fetchAll(
                "SELECT *
            FROM `satellite_css`
            WHERE `satelliteId` = ?", $this->getId()
        );

        $css = array();
        foreach ($rows as $r) {
            $css[$r['name']] = $r['value'];
        }
        return $css;
    }

    /**
     * Edit css properties
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.05.2011
     * @param array $properties 
     * @return void
     */
    public function editCssProperties(array $properties) {

        $db = $this->getAdapter();

        foreach ($properties as $name => $value) {
            try {
                $db->query(
                        "INSERT INTO `satellite_css` (`satelliteId`, `name`, `value`)
                    VALUES (?, ?, ?) ", array($this->getId(), $name, $value)
                );
            } catch (Zend_Db_Statement_Exception $e) {
                $db->query(
                        "UPDATE `satellite_css` 
                    SET `value` = ?, `updated` = NOW()
                    WHERE `satelliteId` = ?
                        AND `name` = ?", array($value, $this->getId(), $name)
                );
            }
        }
    }
}