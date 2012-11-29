<?php

/**
 * Model Servicetype Categories
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Categories extends Default_Model_Base {

    /**
     * Get all
     * @author vpriem
     * @since 21.03.2011
     * @return array
     */
    public static function all() {

        $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        return $dbTable->fetchAll();
    }

    /**
     * Get all categories by plz
     * @author vpriem
     * @since 21.03.2011
     * @param int $cityId
     * @param int $servicetypeId
     * @return array
     */
    public static function getCategoriesByCityId($cityId, $servicetypeId = 1) {
        if (is_string($servicetypeId)) {
            switch ($servicetypeId) {
                default:
                case 'rest':
                    $servicetypeId = 1;
                    break;

                case 'cater':
                    $servicetypeId = 2;
                    break;

                case 'great':
                    $servicetypeId = 3;
                    break;
            }
        }
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        return $dbTable->getCategoriesByCityId($cityId, $servicetypeId);
    }

    /**
     * Get table
     * @author vpriem
     * @since 21.03.2011
     * @return Yourdelivery_Model_DbTable_Restaurant_Categories
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        }
        return $this->_table;
    }

}
