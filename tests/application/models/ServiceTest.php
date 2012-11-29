<?php

/**
 * Description of ServiceTest
 *
 * @author mlaug
 */

/**
 * @runTestsInSeparateProcesses
 */
class ServiceTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
    }

    /**
     * check if the count down is used
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testNotfiyPayedCountDown() {
        $service = $this->getRandomService(array('type' => null, 'onlinePayment' => true));
        $service->setNotifyPayed(1);
        $service->save();

        //no online payment

        $orderId = $this->placeOrder(array('payment' => 'bar', 'service' => $service));

        $service = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());
        $this->assertEquals(1, $service->getNotifyPayed(), $orderId);

        $orderId = $this->placeOrder(array('payment' => 'paypal', 'service' => $service));

        $service = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());
        $this->assertEquals(0, $service->getNotifyPayed(), $orderId);
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testUrlHistory() {

        $service = $this->getRandomService();
        $serviceId = $service->getId();
        $oldRestUrl = $service->getRestUrl();
        $newUrl = 'testRestUrl-' . $service->getId();

        //set New Url
        $this->assertTrue($service->checkOldUrls($newUrl));
        $service->setRestUrl($newUrl);
        $this->assertEquals($service->getRestUrl(), $newUrl);
        $service->save();

        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('restaurant_url_history')->where('restaurantId = ?', $service->getId())->where('url = ? ', $oldRestUrl);

        $result = $db->fetchAll($select);
        //check if old is in history
        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0]['url'], $oldRestUrl);

        $service->buildRedirectCache();

        $this->assertTrue(file_exists(APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $oldRestUrl . ".php"));

        //set back old Url
        $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        $this->assertTrue($service->checkOldUrls($oldRestUrl));
        $service->setRestUrl($oldRestUrl);
        $service->save();

        $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);

        $this->assertEquals($oldRestUrl, $service->getRestUrl());
        //check if everything is back to normal
        $select = $db->select()->from('restaurant_url_history')->where('restaurantId = ?', $service->getId())->where('url = ? ', $newUrl);
        $result = $db->fetchAll($select);
        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0]['url'], $newUrl);
        $this->assertFalse(file_exists(APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $oldRestUrl . ".php"));
        //clean up history

        $db->query('DELETE FROM restaurant_url_history WHERE restaurantId = ' . $serviceId);
    }


    /**
     * @author Jens Naie <naie@lieferando.de>
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testIsNoContract($cache) {
        $this->setUsingCache($cache);
        
        $db = Zend_Registry::get('dbAdapter');
        
        // Normal franchise type "Butler"
        $db->query("delete from restaurant_franchisetype where id = 4");
        $db->query("insert into restaurant_franchisetype (id, name) values (4, 'Butler')");
        if(isSet($this->config->franchise)) {
            unset($this->config->franchise);
        }
        
        $service = $this->getRandomService();

        $service->setFranchiseTypeId(1);
        $this->assertFalse($service->isNoContract());
        
        $service->setFranchiseTypeId(2);
        $this->assertTrue($service->isNoContract());
        
        $service->setFranchiseTypeId(4);
        $this->assertFalse($service->isNoContract());
        
        // Brasilian franchise type "OfflinePayment"
        $db->query("delete from restaurant_franchisetype where id = 4");
        $db->query("insert into restaurant_franchisetype (id, name) values (4, 'OfflinePayment')");
        $this->config->franchise = new Zend_Config(array('noContractIds' => array(2, 4)));

        $service->setFranchiseTypeId(1);
        $this->assertFalse($service->isNoContract());
        
        $service->setFranchiseTypeId(2);
        $this->assertTrue($service->isNoContract());
        
        $service->setFranchiseTypeId(4);
        $this->assertTrue($service->isNoContract());
    }

    /**
     * test deleting and setting of tags
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 16.08.2012
     */
    public function testTags() {
        $service = $this->getRandomService();
        $service->removeAllTags();
        
        // for the case if there are no tags at all in the database, create five
        $createdTagsIds = array();
        $tagTable = new Yourdelivery_Model_DbTable_Tag();
        for ($i = 0; $i < 5; $i++) {
            $row = $tagTable->createRow(array('name' => 'Tag-' . Default_Helper::generateRandomString(10)));
            $row->save();
            $createdTagsIds[] = $row['id'];
        }
                
        // there must be no tags at all, no matter how much the threshold is
        $tags = $service->getTagsWithMaxStringlength(10000);
        $this->assertEquals(count($tags), 0);

        // select three random tags
        $allTags = $service->getAllTagsWithFlag();        
        shuffle($allTags);
        $selectedTags = array_slice($allTags, 0, 3);

        foreach ($selectedTags as $st) {
            $service->addTag($st['id']);            
        }
        
        // now we have some tags
        $tags = $service->getTagsWithMaxStringlength(20000);
        $this->assertEquals(count($selectedTags), count($tags));
        
        // test that these are the correct tags
        $globalFound = true;
        
        foreach ($service->getAllTagsWithFlag() as $globalTag) {
            if ($globalTag['tagAssoc'] > 0 ) {
                // test if this special tag is really set
                $localFound = false;
                foreach ($tags as $tagName) {
                    if (strcmp($globalTag['name'], $tagName) == 0) {
                        $localFound = true;
                        break;
                    }
                }
                
                $globalFound = $globalFound && $localFound;
            }
        }
        
        $this->assertTrue($globalFound);
        
        // remove the tags we've created in this test
        foreach ($createdTagsIds as $tagId) {
            Yourdelivery_Model_DbTable_Tag::remove($tagId);
        }
    }
    
}

?>
