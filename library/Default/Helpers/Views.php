<?php

/**
 * Description of Default_Helpers_Views
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Default_Helpers_Views {

    
    /**
     * get data from given table / view
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.11.2010
     * @param string $view
     * @param string $order
     * @return Zend_DbTable_Rowset
     */
    public static function getViewData($view = null, $order = null){

        if( is_null($view) ){
            return null;
        }

        

        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( $view )
                    ->order($order);

        return $db->fetchAll($query);
    }

}
?>
