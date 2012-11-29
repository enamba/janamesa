<?php
/**
 * This is an anonymous user
 *
 * @author mlaug
 */
class Yourdelivery_Model_Anonym extends Yourdelivery_Model_Customer_Abstract{

    /**
     *
     * @var Yourdelivery_Model_Rabatt
     */
    protected $_rabatt = null;

    /**
     * @author mlaug
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        $value = parent::__get($name);
        if ( $value === false ){
            return false;
        }
        return $value;
    }
    
    /**
     * no table is associated with this object
     * @author mlaug
     * @return null
     */
    public function getTable() {
        return null;
    }

    /**
     * generates a id based on current time
     * this getter has to be overwritten, so that getId can be used
     * to store informations e.g. card without being logged in
     * @author mlaug
     * @return int
     */
    public function getId(){
        if ( is_null($this->_id) ){
            $this->_id = time();
        }
        return $this->_id;
    }

    /**
     * always returns false to ensure no one
     * thinks this anonymous user actually is a ordinary user
     * @author mlaug
     * @return boolean
     */
    public function isLoggedIn(){
        return false;
    }

    /**
     * no persistent messages are possible for anonymous user
     * @return void
     */
    public function setPersistentNotfication(){
        return null;
    }

    /**
     * create a message
     * @author mlaug
     */
    public function onlyLoggedInUsers(){
        $this->error(__('Diese Funktion steht nur angemeldeten Benutzern zur Verfügung.'));
    }
    
    /**
     * removes fidelitypoint(s) from customer
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.08.2010
     * @param int $count
     * @param int $orderId
     * @param string $comment
     * @return int new count of points
     */
    public function removeFidelityPoint($count = 1, $orderId = null, $comment = null){
      
        $fid = Yourdelivery_Model_DbTable_Customer_Fidelity::findByEmail($this->getEmail());
        if(!$fid){
            return null;
        }
        try{
            $fidelity = new Yourdelivery_Model_Customer_Fidelity($fid['id']);
        }catch(Yourdelivery_Exception_Database_Inconsistency $e){
            return null;
        }

        $fidelity->setPoints((intval($fidelity->getPoints())-$count));
        $fidelity->setEdited(date('Y-m-d H:i:s'));
        $fidelity->save();

        //save transaction
        $transaction = new Yourdelivery_Model_Customer_FidelityTransaction();
        $data = array();
        $data['email'] = $this->getEmail();
        $data['transaction'] = '-'.$count;
        $data['orderId'] = $orderId;
        $data['comment'] = $comment;
        $transaction->setData($data);
        $transaction->save();

        return $fidelity->getPoints();
    }

    
    /**
     * set fidelity points count to given count
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.08.2010
     * @param int $count
     * @param int $orderId
     * @param string $comment
     * @return int new count of points
     */
    public function setFidelityPoint($count, $orderId = null, $comment = null){
        if( is_null($count) ){
            return null;
        }

        $fid = Yourdelivery_Model_DbTable_Customer_Fidelity::findByEmail($this->getEmail());
        try{
            $fidelity = new Yourdelivery_Model_Customer_Fidelity($fid['id']);
        }catch(Yourdelivery_Exception_Database_Inconsistency $e){
            return null;
        }

        $values = array();
        $values['points'] = $count;
        $values['edited'] = date('Y-m-d H:i:s');


        $fidelity->setEmail($this->getEmail());
        $fidelity->setPoints($values['points']);
        $fidelity->save();

        //save transaction
        $transaction = new Yourdelivery_Model_Customer_FidelityTransaction();
        $data = array();
        $data['email'] = $this->getEmail();
        $data['transaction'] = 'set to '.$count;
        $data['orderId'] = $orderId;
        $data['comment'] = $comment;
        $transaction->setData($data);
        $transaction->save();

        return $fidelity->getPoints();
    }


    /**
     * in model Anonym always return false
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2010
     * @return boolean
     */
    public function isRegistered(){
        return false;
    }


    /**
     * gives always true, because we want to show premium services to all
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.09.2010
     * @return boolean
     */
    public function isPremium(){
        return true;
    }

}
?>
