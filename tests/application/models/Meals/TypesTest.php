<?php
/**
 * @runTestsInSeparateProcesses 
 */
class TypesTest extends Yourdelivery_Test {
    /**
     * @author alex
     * @since 21.09.2011
     */
    public function testDeadlock() {
        $db = Zend_Registry::get('dbAdapter');
        $types = $db->fetchAll('select id from meal_types');
        
        foreach ($types as $t) {
            $level = 0;
            $type = new Yourdelivery_Model_Meal_Type($t['id']);
            
            while(!is_null($type)) {
                $type = $type->getParent();
                $level++;
                
                if ($level == 5) {
                    $this->assertTrue('Nesting level 5 is reached');
                    return;                    
                }
            }
        }
    }    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.04.2012 
     */
    public function testParentTypes(){
        // non existing parentId
        $types = Yourdelivery_Model_Meal_Type::getTypesOfParent(rand(99999999, 111111111));
        $this->assertInstanceof(SplObjectStorage, $types);
        $db = $this->_getDbAdapter();
        $select = $db->select()->from('meal_types')->where('parentId > 0')->order('RAND()')->limit(1);
        $row = $db->fetchRow($select);
        
        // existing parentId
        $types = Yourdelivery_Model_Meal_Type::getTypesOfParent($row['parentId']);
        $this->assertInstanceof(SplObjectStorage, $types);
        $types->rewind();
        $this->assertInstanceof(Yourdelivery_Model_Meal_Type, $types->current());
        
        // parentId = 0
        $types = Yourdelivery_Model_Meal_Type::getTypesOfParent(0);
        $this->assertInstanceof(SplObjectStorage, $types);
        
        // without ID
        $types = Yourdelivery_Model_Meal_Type::getTypesOfParent();
        $this->assertInstanceof(SplObjectStorage, $types);
        
    }
}
