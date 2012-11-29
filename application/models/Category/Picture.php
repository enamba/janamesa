<?php

/**
 * Description of Picture
 * @package service
 * @subpackage menu
 * @author mlaug
 */
class Yourdelivery_Model_Category_Picture extends Default_Model_Base{

    public function  __construct($id = null){
        parent::__construct($id);
        if ( !is_null($id) ){
            $this->_storage = new Default_File_Storage();
            $this->_storage->setSubFolder(array('category_pictures',$this->getId()));
        }
    }

    
    /**
     * get associated table
     * @return Yourdelivery_Model_DbTable_Category_Picture
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Category_Picture();
        }
        return $this->_table;
    }


    /**
     * get images for this category
     * @author mlaug
     * @return array
     */
    public function getImgs(){
        
        if ( is_null($this->getId()) ){
            return null;
        }

        $d = APPLICATION_PATH . '/../storage/category_pictures/' . $this->getId().'/';

        $pics = array();

        $dir = opendir($d);

        while($file = readdir($dir)){
            if ( $file != "." && $file != ".." && $file != ".svn" && $file != ".DS_Store" ) {
                $pics[] = '/../storage/category_pictures/' . $this->getId() . '/' . $file;
            }
        }

        return $pics;
    }

    /**
     * return randomly images from the pool
     * @author mlaug
     * @return string
     */
    public function getRandomImg(){
        $pics = $this->getImgs();
        if ( count($pics) == 0){
            return null;
        }
        // seed random function
        mt_srand((double)microtime()*1000000);
        // get an random index:
        $rand = mt_rand(0, count($pics)-1);
        return $pics[$rand];
    }

    
    /**
     * store image for picture category
     * @author mlaug
     * @param string $name
     * @return boolean
     * @modified Alex Vait 07.12.2011
     */
    public function setImg($name){
        if(is_null($this->getId())){
            return false;
        }

        $file = file_get_contents($name);

        if ( $file !== false ) {
            $name = 'cat-'.$this->getId().'-'.time().'.jpg';
            $this->getStorage()->store($name, $file);
            
            // update pictures for associated meal categories
            $this->updateAssociatedCategories();
            
            return true;
        }
        return false;
    }
    
    /**
     * set new pictures to all associated meal categories if a picture for this category 
     * was deleted or some new picture uploded
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.12.2011
     */
    public function updateAssociatedCategories(){
        foreach ($this->getTable()->getAssociatedCategories() as $meal_category) {
            try{
                $cat = new Yourdelivery_Model_Meal_Category($meal_category['id']);
                $img = $cat->getImage(true);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e){
                continue;
            }            
        }
    }    
}
?>
