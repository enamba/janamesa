<?php
/**
 * Restaurant Benchmark Db Table
 * @author vpriem
 * @since 14.12.2010
 */
class Yourdelivery_Model_DbTable_Restaurant_Benchmark extends Default_Model_DbTable_Base{

    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Restaurant_Benchmark';

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_benchmark';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Find row by ort
     * @author vpriem
     * @since 23.11.2010
     * @param int $city
     * @return Yourdelivery_Model_DbTableRow_Restaurant_Benchmark
     */
    public function findByCity ($city) {

        return $this->fetchRow(
            $this->select()
                ->where("`city` = ?", array($city))
        );

    }

}

/**
 * Restaurant Benchmark Db Table Row
 * @author vpriem
 * @since 14.12.2010
 */
class Yourdelivery_Model_DbTableRow_Restaurant_Benchmark extends Zend_Db_Table_Row_Abstract{

    /**
     * Pre-update
     * @author vpriem
     * @since 14.12.2010
     * @return void
     */
    protected function _update(){

        $this->updated = date(DATETIME_DB);

    }

}