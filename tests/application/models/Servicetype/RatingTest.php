<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 05.03.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_RatingTest extends Yourdelivery_Test {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.04.2012
     * @return Yourdelivery_Model_Servicetype_Rating
     */
    public function getRandomRating() {

        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow('SELECT `id` FROM `restaurant_ratings` ORDER BY RAND() LIMIT 1');

        $rating = new Yourdelivery_Model_Servicetype_Rating($row['id']);
        $this->assertTrue($rating instanceof Yourdelivery_Model_Servicetype_Rating);

        return $rating;
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.03.2012
     * 
     * @dataProvider dataProviderCacheNoCache 
     */
    public function testAuthorImage($useCache){
        $this->setUsingCache($useCache);
        
        // place unregistered order
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $rating = $this->createRating($order);
        $this->assertFalse($order->getCustomer()->isPersistent());
        
        // check for default image URL
        $this->assertEquals('http://cdn.yourdelivery.de/images/yd-profile/default_user.png', $rating->getAuthorImage());
        
        // get random customer, add profile image
        $customer = $this->getRandomCustomer();
        $file = APPLICATION_PATH. '/../tests/data/samson.jpg';
        $this->assertFileExists($file);
        $customer->addImage($file, true);
        
        $customer = new Yourdelivery_Model_Customer($customer->getId());
        $this->assertNotNull($customer->getProfileImage());
        $config = Zend_Registry::get('configuration');
        
        // place registered order
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        $rating = $this->createRating($order);
        $this->assertTrue($order->getCustomer()->isPersistent());
        
        $profileImageTestingUrl = $customer->getProfileImage();
        $profileImageTestingUrl = str_replace(sprintf('%s/customer', $config->domain->base), sprintf('%s.testing/customer', $config->domain->base), $profileImageTestingUrl);
        
        $ratingAuthorImageTestingUrl = $rating->getAuthorImage();
        $ratingAuthorImageTestingUrl = str_replace(sprintf('%s/customer', $config->domain->base), sprintf('%s.testing/customer', $config->domain->base), $ratingAuthorImageTestingUrl);
        
        $this->assertTrue(Default_Helpers_Web::url_exists($profileImageTestingUrl), sprintf('profileImage for customer #%d %s does not exist at url "%s"', $customer->getId(), $customer->getFullname()));
        $this->assertEquals($profileImageTestingUrl, $ratingAuthorImageTestingUrl, sprintf('failed to get identical image for customer #%d %s as ProfileImage (%s) and RatingAuthorImage (%s)', $customer->getId(), $customer->getFullname(), $profileImageTestingUrl, $ratingAuthorImageTestingUrl));
        
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.04.2012
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testCrm($useCache) {
        $this->setUsingCache($useCache);
        
        $rating = $this->getRandomRating();
        $this->assertTrue(is_array($rating->getCrmLogs()));
        $count = count($rating->getCrmLogs());
        
        $crmId = $rating->logCrm(5, "MyCall");
        $this->assertGreaterThan(0, $crmId);
        $crms = $rating->getCrmLogs();
        $this->assertEquals($count + 1, count($crms));
        $this->assertTrue($crms[$count] instanceof Yourdelivery_Model_Servicetype_Rating_Crm);
        $this->assertEquals($crmId, $crms[$count]->getId());
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012 
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testRatingGetList($useCache){
        $this->setUsingCache($useCache);
        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $service = $order->getService();
        $order->rate(null, 5, 5, 'test', 'test', true, 'Mattes');
        $service->getRating()->clearCache();
        $this->assertIsArray($service->getRating()->getList());
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012 
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testRatingHasRating($useCache){
        $this->setUsingCache($useCache);
        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $service = $order->getService();
        $order->rate(null, 5, 5, 'test', 'test', true, 'Mattes');
        $service->getRating()->clearCache();
        $this->assertTrue($service->getRating()->hasRating());
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012 
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testRatingGetCount($useCache){
        $this->setUsingCache($useCache);
        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $service = $order->getService();
        $countBefore = $service->getRating()->count();
        $order->rate(null, 5, 5, '', '', true, 'Mattes');
        $service->getRating()->clearCache();
        $this->assertEquals($countBefore+1,$service->getRating()->count(), sprintf('rating count was not correct for service #%d', $service->getId()));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012 
     * 
     * @dataProvider dataProviderCacheNoCache
     */ 
    public function testRatingGetAverage($useCache){
        $this->setUsingCache($useCache);
        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $service = $order->getService();
        $averageBefore = $service->getRating()->getAverage();
        $averageAdviseBefore = $service->getRating()->getAverageAdvise();
        $averageQualityBefore = $service->getRating()->getAverageQuality();
        $averageDeliveryBefore = $service->getRating()->getAverageDelivery();
        $order->rate(null, 5, 5, '', '', true, 'Mattes');
        $service->getRating()->clearCache();
        $this->assertGreaterThanOrEqual($averageBefore, $service->getRating()->getAverage(), $service->getId());
        $this->assertGreaterThanOrEqual($averageAdviseBefore ,$service->getRating()->getAverageAdvise(), $service->getId());
        $this->assertGreaterThanOrEqual($averageQualityBefore, $service->getRating()->getAverageQuality(), $service->getId());
        $this->assertGreaterThanOrEqual($averageDeliveryBefore, $service->getRating()->getAverageDelivery(), $service->getId());
    }
    
}
