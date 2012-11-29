<?php
/**
 * Description of CourierTest
 *
 * @author 
 */

/**
 * @runTestsInSeparateProcesses 
 */
class Administration_CourierTest extends Yourdelivery_Test {

    protected static $cityId;
    protected static $courierId;

    public function setUp() {
        parent::setUp();
        
        if(HOSTNAME == 'lieferando.at' || HOSTNAME == 'lieferando.ch' || HOSTNAME == 'smakuje.pl'){
            $this->markTestSkipped("in AT, CH, PL we don't have couriers yet");
        }
        
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author
     * @todo implement
     */
    public function testEditPlzs() {

        $courier = $this->getRandomCourier();
        $request = $this->getRequest();
        $request->setMethod('GET');

        $url = '/administration_courier/editplzs/cid/' . $courier->getId();
        $this->dispatch($url);

        $response = $this->getResponse();
        $this->assertEquals('200', $response->getHttpResponseCode());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * 
     */
    public function testAddPlz() {

        $courier = $this->getRandomCourier();
        $city = $this->getRandomCityId();
        self::$cityId = $city;
        self::$courierId = $courier->getId();
        $post = array('cid' => $courier->getId(),
            'cityId' => $city,
            'delcost' => 12,
            'deliverTime' => 12,
            'mincost' => 12
        );

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);
        $url = '/administration_courier/addplz';

        $this->dispatch($url);

        $this->assertRedirect('/administration_courier/editplzs/cid/' . $courier->getId());
        $plzs = $this->getCityIdByCourierId($courier->getId());

        $this->assertTrue(in_array($city, $plzs));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @depend testAddPlz
     */
    public function testDeletePlz() {

        if (!empty(self::$cityId) && !empty(self::$courierId)) {

            $db = Zend_Registry::get('dbAdapter');
            $select = $db->select()->from(array('c' => 'courier_plz'), array('c.id')
                    )->where('c.cityId = ?', self::$cityId);

            $result = $db->fetchAll($select);


            $request = $this->getRequest();
            $request->setMethod('GET');
            $url = "/administration_courier/deleterange/id/" . self::$courierId . "/rangeId/" . $result[0]['id'];
            $this->dispatch($url);

            $this->assertRedirect('/administration_courier/editplzs/cid/' . self::$courierId);

            $plzs = $this->getCityIdByCourierId(self::$courierId);


            $this->assertTrue(!in_array(self::$cityId, $plzs));
        }
    }

}

?>
