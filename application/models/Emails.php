<?php
/**
 * Store all send out emails
 * @package backend
 * @author mlaug
 */
class Yourdelivery_Model_Emails extends Default_Model_Base{

    /**
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Emails
     */
    public function getTable(){
        
        if ($this->_table === null){
            $this->_table = new Yourdelivery_Model_DbTable_Emails();
        }
        return $this->_table;
    }
    
    /**
     * get the content of this email from hdd
     * @since 13.03.2011
     * @author mlaug
     * @return string
     */
    public function getContent(){
        
        if ($this->getId() === null){
            return null;
        }
        
        // get one of the receipients
        $email = current(explode(',', $this->getEmail()));
        
        // get html content file
        $path =  substr($email,0,1) . '/' . substr($email,1,1) . '/';
        $html = APPLICATION_PATH . '/../storage/emails/' . $path . $email . '-' . $this->getId() . '.html';
        if (file_exists($html)) {
            return file_get_contents($html);
        }
        
        $this->logger->warn('could not find email content for row ' . $this->getId());
        return null;
    }

}
