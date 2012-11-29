<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_Salechannel_Cost extends Default_Model_Base {

    /**
     * @author mlaug
     * @since 03.05.2011
     */
    public function generateData($groupedBy = 'daily'){
        $base = $this->getTable()->generateData($groupedBy);
        
        //we grouped by days, so the count of the array
        //is the count of found days
        $countDays = count($base);
        
        $merged_data = array();
        foreach($base as $day => $data){
            $data['impression'] = 0; //TODO
            $data['visitors'] = 0; //TODO
            $data['ctr'] = 0; //TODO
            $data['cost'] = $this->getCost() / ($countDays > 0 ? $countDays : 1);
            $data['return'] = $data['conversion'] - $data['cost'];
            $data['roi'] = $data['return'] / ($data['cost'] > 0 ? $data['cost'] : 1);
            $data['voc'] = 0; //TODO;
            $merged_data[] = $data;
        }
        
        return $merged_data;
        
    }
    
    /**
     * @author mlaug
     * @since 03.05.2011
     * @return Yourdelivery_Model_DbTable_Salechannel_Cost
     */
    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Salechannel_Cost();
        }
        return $this->_table;
    }

}