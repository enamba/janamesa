<?php
/**
 * @runTestsInSeparateProcesses 
 */
class RequestControllerTest extends Yourdelivery_Test {

    private static $randomEmail = '';
    private $_password = 'samsontiffy';

    /**
     * Generates a new random Email like 'DTVCnqIkfAnX0@test.de'
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public static function generateRandomEmail() {
        self::$randomEmail = Default_Helper::generateRandomString(13) . "@test.de";
    }

    /**
     * Returns the generated Email from generateRandomEmail()
     * @author Allen Frank <frank@lieferando.de>
     * @since 11.03.11
     */
    public static function getRandomEmail() {
        return self::$randomEmail;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.06.2011
     * 
     * @todo implement
     */
    public function testAddLocationsSuccess() {

        $this->markTestIncomplete('HAS TO BE IMPLEMENTED');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.06.2011
     * 
     * @todo implement
     */
    public function testAddLocationsFail() {

        $this->markTestIncomplete('HAS TO BE IMPLEMENTED');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.06.2011
     * 
     * @todo implement
     */
    public function testDeleteLocationsSuccess() {

        $this->markTestIncomplete('HAS TO BE IMPLEMENTED');
    }

    public function testNewPassSuccess() {

        $customer = $this->getRandomCustomer();

        $pass = $customer->getPassword();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $customer->getEmail(),
        ));
        $this->dispatch('/request/newpass');

        $this->assertEquals('200', $this->getResponse()->getHttpResponseCode());

        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()->from('customers')->where('id = ?', $customer->getId());

        $stmt = $select->query();
        $result = $stmt->fetchAll();

        $this->assertNotEquals(trim($result[0]['password']), trim($pass));
    }

    public function testNewPassError() {
        $email = $this->getRandomEmail();


        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $email,
        ));
        $this->dispatch('/request/newpass');

        $this->assertEquals('200', $this->getResponse()->getHttpResponseCode());

        $body = json_decode($this->getResponse()->getBody(), true);

        $this->assertArrayHasKey('error', $body);
    }

}

?>
