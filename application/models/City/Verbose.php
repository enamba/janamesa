<?php


/**
 * Description of Verbose
 *
 * @author mlaug
 */
class Yourdelivery_Model_City_Verbose extends Default_Model_Base{
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.12.2011
     * @param string $city
     * @return array
     */
    public function getInformation($city){
        return $this->getTable()->getInformation($city);
    }
    
    /**
     * get a list of unique cidades :)
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     * @return array
     */
    public function getCities(){
        $cidades = Default_Helpers_Cache::load('verboseCity');
        if ( $cidades === null ){
            $cidades = $this->getTable()->getCities();
            Default_Helpers_Cache::store('cidades', $cidades);
        }
        return $cidades;
    }
    
    
    /**
     * get a list of endereco :)
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     * @return array
     */
    public function getSteetTypes(){
        $streetTypes = Default_Helpers_Cache::load('verboseStreetTypes');
        if ( $streetTypes === null ){
            $streetTypes = $this->getTable()->getStreetTypes();
            Default_Helpers_Cache::store('verboseStreetTypes', $streetTypes);
        }
        return $streetTypes;
    }
    
    /**
     * find a match
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $city
     * @param string $street 
     * @return array
     */
    public function findmatch($city, $street, $number = null){
        $hash = md5($city . $street . $number);
        $list = Default_Helpers_Cache::load($hash);
        if ( $list === null ){
            $list = $this->getTable()->findmatch($city, $street, $number);
            Default_Helpers_Cache::store($hash, $list);
        }
        return $list;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.12.2011
     * @return Yourdelivery_Model_DbTable_City_Verbose
     */
    public function getTable(){
        if ( $this->_table === null ){
            $this->_table = new Yourdelivery_Model_DbTable_City_Verbose();
        }
        return $this->_table;
    }
    
}

?>
