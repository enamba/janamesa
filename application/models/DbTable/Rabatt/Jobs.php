<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Jobs
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Rabatt_Jobs extends Zend_Db_Table_Abstract {

    /**
     * Table name
     */
    protected $_name = 'rabatt_generation_jobs';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * get all jobs, group to avoid duplicate generation
     * 
     * @author mlaug
     * @since 24.01.2012
     * @return array
     */
    public static function getJobs() {
        $table = new Yourdelivery_Model_DbTable_Rabatt_Jobs();
        return $table->select()
                        ->where('status=0')
                        ->group('rabattId')
                        ->query()
                        ->fetchAll();
    }

    /**
     * finish a certian job for a rabattId
     * 
     * @author mlaug
     * @since 24.01.2012
     * @param type $rabattId
     * @param type $status 
     */
    public static function finishJob($rabattId, $status = 1) {
        $table = new Yourdelivery_Model_DbTable_Rabatt_Jobs();
        $table->update(array(
            'status' => $status
        ), 'rabattId=' . (integer) $rabattId);
    }

    /**
     *
     * @param Yourdelivery_Model_Rabatt $discount
     * @param type $count
     * @param type $email 
     */
    public static function createJob(Yourdelivery_Model_Rabatt $discount, $email, $count) {
        $table = new Yourdelivery_Model_DbTable_Rabatt_Jobs();
        return $table->createRow(array(
                    'rabattId' => $discount->getId(),
                    'count' => (integer) $count,
                    'email' => $email,
                    'status' => 0
                ))->save();
    }

}

?>
