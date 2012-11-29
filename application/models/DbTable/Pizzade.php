<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Yourdelivery_Model_DbTable_Pizzade extends Default_Model_DbTable_Base{
     protected $_name = 'pizzade';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    public static function edit($id, $data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('pizzade', $data, 'pizzade.id = ' . $id);
    }

    public function updateStatus($id,$status){
        $db = Zend_Registry::get('dbAdapter');
        $db->update('pizzade', array('status' => $status), 'pizzade.id = ' . $id);
    }
    public function findExcludeByPlz($plz){
         $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("p" => "pizzade") )
                    ->where("p.plz = '" . $plz . "' and p.shopsystem = '0' and p.status='1' ");


        return $db->fetchAll($query);
    }
    
    public function findExcludeByCity($city){
         $sql= "select * from pizzade p, orte o where p.plz=o.plz and p.shopsystem ='0' and p.status='1' and o.ort='".$city."'";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();
    }
    
    public function findByPlz($plz){
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("p" => "pizzade") )
                    ->where("p.plz = '" . $plz . "' and p.shopsystem = '0'");
                           

        return $db->fetchAll($query);
    }

    public function findIncludeByPlz($plz){
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("p" => "pizzade") )
                    ->where("p.plz = '" . $plz . "' and p.status='0' and p.shopsystem = '0'");


        return $db->fetchAll($query);
    }

     public function findByCity($city){
       
        $sql= "select * from pizzade p, orte o where p.plz=o.plz and p.shopsystem ='0' and o.ort='".$city."'";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();
        

    }
    /*
     * find data for every restaurant that set to be included
     */
    public function findIncludeByCity($city){

        $sql= "select * from pizzade p, orte o where p.plz=o.plz and p.shopsystem ='0' and p.status='0' and o.ort='".$city."'";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();


    }
    /*
     * find data based on given time
     */
    public function findByTime($time){

        $sql= "select * from pizzade where crawtime = '".$time."' and shopsystem = '0'";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();

    }
    /*
     *find data base on given crawler time and which type of shop
     * 0 : have shop system
     * 1 : have no shop system
     */
    public function findByTimeWithShop($time,$shop){

        $sql= "select * from pizzade where crawtime = '".$time."' and shopsystem = '".$shop."' order by name asc";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();

    }
    /*
     * find shop based on selected shop status
     * 0 : have shop system
     * 1 : have no shop system
     */
    public function findByShopsystem($shop){
        $sql= "select * from pizzade where shopsystem = '".$shop."' order by name asc";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();
    }
    public function getLatestNew($time){

        $sql= "select count(*) as TOTAL from pizzade where crawtime = '".$time."'";
        return $this->getAdapter()
                    ->query($sql)
                    ->fetch();


    }

    public function getTopByPlz($limit){
        $sql = "SELECT o.ort, count( p.id ) AS count, o.plz AS Plz FROM orte o LEFT JOIN pizzade p ON o.plz = p.plz and p.shopsystem = '0' GROUP BY o.plz ORDER BY count DESC Limit 0 ,".$limit;
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();

    }
    public function getTopByCity($limit){
        $sql = "SELECT o.ort, count( p.id ) AS count, o.plz AS Plz FROM orte o LEFT JOIN pizzade p ON o.plz = p.plz and p.shopsystem = '0' GROUP BY o.ort ORDER BY count DESC Limit 0 ,".$limit;
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();

    }
    /**
     * this function use to remove duplicate data in table pizzade and insert in to table pizza
     * use on crawlerAction
     */
    public function copyTable(){
        $sql = 'Delete from pizza where id != -1';
        $this->getAdapter()->query($sql);
        $sql = 'Insert into pizza SELECT * FROM pizzade GROUP BY url';
        $this->getAdapter()->query($sql);
    }
    /*
     * this script use on crawler action
     * purpose for restore from pizza table
     */
    public function dropTable(){
        $sql = 'Delete from pizzade where id != -1';
        $this->getAdapter()->query($sql);
    }
    /*
     * this function use to restore restaurant data from table pizza and remove any duplicate data
     * this script use on crawlerAction
     */
    public function restoreTable(){
        $sql = 'Insert into pizzade SELECT * FROM pizza GROUP BY url';
        $this->getAdapter()->query($sql);
    }
    
    public function testData($name){
        $sql = "SELECT * from ".$name;
        return $this->getAdapter()
                    ->query($sql)
                    ->fetchAll();
    }
}

?>
