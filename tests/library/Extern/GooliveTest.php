<?php
/**
 * Testcase for the Goolive API
 *
 * @author Andre Ponert <ponert@lieferando.de>
 * @since 31.07.2012
 *
 * @runTestsInSeparateProcesses
 */
class GooliveTest extends Yourdelivery_Test {

    /**
     * holds API instance
     * @var Goolive_ApiCall
     */
    private $_objGoolive = null;

    /**
     * Sets up the testcase
     *
     * @see Yourdelivery_Test::setUp()
     */
    public function setUp() {
        require_once(APPLICATION_PATH . '/../library/Extern/Goolive/ApiCall.php');
        $this->_objGoolive = new Goolive_ApiCall();
        $this->assertTrue($this->_objGoolive instanceof Goolive_ApiCall);
    }

    /**
     * Provides the TestCase with sample codes and if they are valid or not

     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.07.2012
     *
     * @return array
     */
    public static function codeProvider() {
        return array(
            array('2212031019604329', true),
            array('2212031019604328', true),
            array('110', false),
            array('12345678', false),
            array('ABCDEFGHIJK', false),
            array('%&&$))((=}[[[[]*#<>', false),
        );
    }

    /**
     * Tests the code validation

     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.07.2012
     * @param string $code the code to be checked
     * @param boolean $valid value indicating if code is valid

     * @dataProvider codeProvider
     */
    public function testValidateCode($code, $valid) {
        // validate code and assert that it's validation status is correct
        $result = $this->_objGoolive->validate($code);
        $this->assertEquals($result, $valid);
    }

    /**
     * Tests the getResultCode method
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.07.2012
     */
    public function testResultCode() {
        // We did no request to the API until now. Assert we have no result code
        $this->assertEquals($this->_objGoolive->getResultCode(), 'Genereller Fehler');

        // fire a request, assert we get a result code
        $this->_objGoolive->validate('Just a string producing an invalid code');
        $this->assertNotEquals($this->_objGoolive->getResultCode(), 'Genereller Fehler');
    }

    /**
     * Tests, if the result codes are correct
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 31.07.2012
     */
    public function testInvalidRequests() {

        // general data
        $url = 'http://www.goolive.de/common/api/goocard_validation.php';
        $user = 'lieferando';
        $pass = 'VR0BLBfQpjJK';
        $passWrong = 'a wrong password';

        // calculating auth parameters
        $block = intval(gmmktime() / 60) * 60;
        $auth = md5($pass . 'goolive-api' . $block);
        $authWrong = md5($passWrong . 'goolive-api' . $block);

        // no cardnumber param set
        $withoutCardnumber = $url . '?auth=' . md5($user) . $auth;

        // no auth param set
        $withoutAuth = $url . '?cardnumber=2212031019604329';

        // cardnumber set, but wrong format
        $withoutAuthWrongCardnumber = $url . '?cardnumber=ABCDEF';

        // auth param generated with invalid password
        $authFailure = $url . '?auth=' . md5($user) . $authWrong . '&cardnumber=2212031019604329';

        // now asserting, that the API produces the correct response code
        $result = @file_get_contents($withoutCardnumber);
        $this->assertEquals($result, '-1');

        $result = @file_get_contents($withoutAuth);
        $this->assertEquals($result, '-2');

        $result = @file_get_contents($authFailure);
        $this->assertEquals($result, '-3');

        $result = @file_get_contents($withoutAuthWrongCardnumber);
        $this->assertEquals($result, '-4');
    }
}