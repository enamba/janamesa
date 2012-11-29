<?php
/**
 * @runTestsInSeparateProcesses 
 */
class DbCategoriesTest extends Yourdelivery_Test {
    /*
     * create a random Category
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 22.05.2012
     */

    public function CreateRandomCategory() {
        $cat = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        $data = array('name' => 'CAT-' . date('d-m-y') . ' ' . time(), ' description' => 'this is test');
        $row = $cat->createRow($data);
        $row->save();
        return $row;
    }

    /**
     * test edit, get, findById, findByName, getAll, remove
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 22.05.2012
     */
    public function testAllCategories() {
        $cat = $this->CreateRandomCategory();
        $catId = $cat['id'];
        $category = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        $newDesc = array('description' => 'This is ' . $catId . ' description');
        $category->edit($catId, $newDesc);

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('restaurant_categories')->where('id= ' . $catId);
        $result = $db->fetchRow($query);

        $this->assertEquals($newDesc['description'], $result['description']);

        $this->assertEquals(1, count($category->get('id', 1, 'restaurant_categories')));

        $ResById = $category->findById($catId);
        $this->assertEquals($result, $ResById);

        $this->assertEquals($result, $category->findByName($result['name']));

        $this->assertEquals($result, $category->findByDescription($result['description']));

        $sql = $db->select()->from('restaurant_categories', 'COUNT(id) AS num');
        $countAll = $db->fetchRow($sql);
        $this->assertEquals($countAll['num'], count($category->getAll()));

        $category->remove($catId);
        $this->assertFalse($db->fetchRow($query));
    }

    /**
     * test getCategoriesByCityId function
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 22.05.2012
     */
    public function testgetCategoriesByCityId() {
        $service = $this->createRestaurant();
        $service_data = $service->getData();
        $city_data = $service->getCity()->getData();

        if ($service_data['plz'] == '') {
            $service->setData(array('plz' => $city_data['plz']));
            $service->save();
            $service_data = $service->getData();
        }

        $serviceId = $service->getId();
        $cityId = $city_data['id'];

        //create restaurant service type
        $serviceType = new Yourdelivery_Model_DbTable_Restaurant_Servicetype();
        $row = $serviceType->createRow(array('restaurantId' => $serviceId, 'servicetypeId' => 1));
        $row->save();

        $category = new Yourdelivery_Model_DbTable_Restaurant_Categories();
        $this->assertGreaterThan(0, count($category->getCategoriesByCityId($cityId, 1)));
    }

}

?>
