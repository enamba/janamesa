<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.06.2012
 */
class Yourdelivery_Model_Meal_Type_Nn extends Default_Model_Base {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     * @return Yourdelivery_Model_DbTable_Meal_Types_Nn
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Meal_Types_Nn();
        }

        return $this->_table;
    }

}

