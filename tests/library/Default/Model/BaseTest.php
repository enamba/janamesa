<?php

require_once(APPLICATION_PATH . '/../library/Zend/Db/Table/Abstract.php');
require_once(APPLICATION_PATH . '/../library/Default/Model/DbTable/Base.php');
require_once(APPLICATION_PATH . '/../library/Default/Model/Base.php');

class DbModel extends Default_Model_DbTable_Base {

    protected $_name = 'base_test';

}

class MyModel extends Default_Model_Base {

    public function getTable() {
        if ($this->_table === null) {
            $this->_table = new DbModel();
        }
        return $this->_table;
    }

}

/**
 * Description of BaseTest
 *
 * @author Matthias Laug <laug@lieferando.de>
 */

/**
 * @runTestsInSeparateProcesses
 */
class Default_Model_BaseTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();

        $db = Zend_Registry::get('dbAdapter');
        $db->query('DROP TABLE IF EXISTS `base_test`;');
        $db->query('
            CREATE TABLE `base_test` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `stringtest` VARCHAR(255) DEFAULT NULL,
                `stringtestTwo` VARCHAR(255) NOT NULL DEFAULT "eldiablo",
                `stringtestThree` VARCHAR(255) NOT NULL,
                `inttest` INT DEFAULT NULL,
                `texttest` TEXT DEFAULT NULL,
                `datetest` DATETIME NULL,
                `companyId` INT DEFAULT NULL,
                `customerId` INT DEFAULT NULL,
                `restaurantId` INT DEFAULT NULL
            ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
        ');

        $db->query('alter table `base_test`  add unique `uqString` (`stringtest`);');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testMagicSetter() {
        $model = new MyModel();
        $model->setStringtest('samson');
        $this->assertEquals('samson', $model->getStringtest());
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $storedModel = new MyModel($id);
        $this->assertEquals('samson', $storedModel->getStringtest());
    }

    /**
     * check all uses of the magic save method
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.02.2012
     */
    public function testIntelligentSaveMethod() {

        $model = new MyModel();
        $model->setStringtest('samson');
        $this->assertEquals('samson', $model->getStringtest());
        $this->assertFalse($model->isPersistent());
        $this->assertTrue($model->getCompany() instanceof Yourdelivery_Model_Company);
        $this->assertTrue($model->getService() instanceof Yourdelivery_Model_Servicetype_Abstract);
        $this->assertTrue($model->getCustomer() instanceof Yourdelivery_Model_Customer_Abstract);
        $id = $model->save();
        $this->assertTrue($model->isPersistent());
        $this->assertGreaterThan(0, $id);

        $storedModel = new MyModel($id);
        $this->assertTrue($storedModel->isPersistent());
        $storedModel->setStringtest('tiffy');
        $customer = $this->getRandomCustomer();
        $storedModel->setCustomer($customer);
        $service = $this->getRandomService();
        $storedModel->setRestaurant($service);
        $company = $this->getRandomCompany();
        $storedModel->setCompany($company);
        $this->assertEquals('tiffy', $storedModel->getStringtest());
        $storedModel->save();

        //update again
        $storedModel->setStringtest('samsontiffy');
        $storedModel->save();
        $this->assertEquals('samsontiffy', $storedModel->getStringtest());
        $storedModel->setStringtest('');
        $storedModel->setInttest(0);
        $storedModel->save();
        $this->assertTrue('' === $storedModel->getStringtest());
        $this->assertTrue(0 === $storedModel->getInttest());

        $storedModel->setStringtest(NULL);
        $storedModel->save();
        unset($storedModel);
        $storedModel = new MyModel($id);
        $this->assertEquals(NULL, $storedModel->getStringtest());

        $storedModel->setStringtest('samsontiffy');
        $storedModel->save();
        $this->assertEquals('samsontiffy', $storedModel->getStringtest());


        unset($storedModel);
        $storedModel = new MyModel($id);
        $this->assertEquals('samsontiffy', $storedModel->getStringtest());
        $this->assertTrue($storedModel->getCompany() instanceof Yourdelivery_Model_Company);
        $this->assertEquals($storedModel->getCompany()->getId(), $company->getId());
        $this->assertTrue($storedModel->getService() instanceof Yourdelivery_Model_Servicetype_Abstract);
        $this->assertEquals($storedModel->getService()->getId(), $service->getId());
        $this->assertTrue($storedModel->getCustomer() instanceof Yourdelivery_Model_Customer_Abstract);
        $this->assertEquals($storedModel->getCustomer()->getId(), $customer->getId());

        $nextCustomer = $this->getRandomCustomer();
        $this->assertNotEquals($nextCustomer->getId(), $customer->getId());
        $storedModel->setCustomer($nextCustomer);
        $storedModel->save();

        unset($storedModel);
        $storedModel = new MyModel($id);
        $this->assertTrue($storedModel->getCustomer() instanceof Yourdelivery_Model_Customer_Abstract);
        $this->assertEquals($storedModel->getCustomer()->getId(), $nextCustomer->getId());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     */
    public function testSetData() {

        // test insert
        $model = new MyModel();
        $model->setData(array(
            'stringtest' => "tiffy",
            'inttest' => 21,
            'texttest' => "yeah",
        ));
        $this->assertEquals('tiffy', $model->getStringtest());
        $this->assertEquals('21', $model->getInttest());
        $this->assertEquals('yeah', $model->getTexttest());
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $model = new MyModel($id);
        $this->assertEquals('tiffy', $model->getStringtest());
        $this->assertEquals('21', $model->getInttest());
        $this->assertEquals('yeah', $model->getTexttest());

        // test update
        $model = new MyModel($id);
        $this->assertEquals('tiffy', $model->getStringtest());
        $this->assertEquals('21', $model->getInttest());
        $this->assertEquals('yeah', $model->getTexttest());
        $model->setData(array(
            'stringtest' => "samson",
            'inttest' => 84,
            'texttest' => "Yippee-kai-yay",
        ));
        $this->assertTrue($model->save());
        $model = new MyModel($id);
        $this->assertEquals('samson', $model->getStringtest());
        $this->assertEquals('84', $model->getInttest());
        $this->assertEquals('Yippee-kai-yay', $model->getTexttest());

        $model = new MyModel();
        $model->setData(array(
            'stringtest' => null,
            'inttest' => 21,
            'texttest' => "yeah",
        ));
        $model->save();


        $checkModel = new MyModel($model->getId());
        $this->assertNull($checkModel->getStringtest());

        $model = new MyModel();
        $model->setData(array(
            'stringtest' => 0,
            'inttest' => 21,
            'texttest' => "yeah",
        ));
        $model->save();

        $checkModel = new MyModel($model->getId());
        $this->assertEquals($checkModel->getStringtest(), "0");
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.03.2012
     */
    public function testInsert() {

        $model = new MyModel();
        $model->setStringtest('');
        $model->setInttest(0);
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $model = new MyModel($id);
        $this->assertTrue('' === $model->getStringtest());
        $this->assertTrue("0" === $model->getInttest());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.03.2012
     */
    public function testInsertDefault() {

        $model = new MyModel();
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $this->assertEquals(NULL, $model->getStringtest());
        $this->assertEquals('eldiablo', $model->getStringtestTwo());
        $this->assertEquals('', $model->getStringtestThree());
        $model = new MyModel($id);
        $this->assertEquals(NULL, $model->getStringtest());
        $this->assertEquals('eldiablo', $model->getStringtestTwo());
        $this->assertEquals('', $model->getStringtestThree());

        $model = new MyModel();
        $model->setStringtest('eldios');
        $model->setStringtestTwo('eldios');
        $model->setStringtestThree('eldios');
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $model = new MyModel($id);
        $this->assertEquals('eldios', $model->getStringtest());
        $this->assertEquals('eldios', $model->getStringtestTwo());
        $this->assertEquals('eldios', $model->getStringtestThree());

        $model = new MyModel();
        $model->setStringtest(NULL);
        $model->setStringtestTwo(NULL);
        $model->setStringtestThree(NULL);
        $id = $model->save();
        $this->assertGreaterThan(0, $id);
        $model = new MyModel($id);
        $this->assertEquals(NULL, $model->getStringtest());
        $this->assertEquals('eldiablo', $model->getStringtestTwo());
        $this->assertEquals('', $model->getStringtestThree());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.03.2012
     */
    public function testUpdateDefault() {

        $model = new MyModel();
        $id = $model->save();
        $this->assertGreaterThan(0, $id);

        $model = new MyModel($id);
        $this->assertEquals(NULL, $model->getStringtest());
        $this->assertEquals('eldiablo', $model->getStringtestTwo());
        $this->assertEquals('', $model->getStringtestThree());
        $model->setStringtest('eldios');
        $model->setStringtestTwo('eldios');
        $model->setStringtestThree('eldios');
        $model->save();

        $model = new MyModel($id);
        $this->assertEquals('eldios', $model->getStringtest());
        $this->assertEquals('eldios', $model->getStringtestTwo());
        $this->assertEquals('eldios', $model->getStringtestThree());
        $model->setStringtest(NULL);
        $model->setStringtestTwo(NULL);
        $model->setStringtestThree(NULL);
        $model->save();

        $model = new MyModel($id);
        $this->assertEquals(NULL, $model->getStringtest());
        $this->assertEquals('eldiablo', $model->getStringtestTwo());
        $this->assertEquals('', $model->getStringtestThree());
    }

    /**
     * we have a isXXX capsulated in the __call. Here we can use isBloomsburys
     * we should consider that some names to inherit none ascii chars
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.05.2012
     */
    public function testIsFranchiseCall() {
        $this->markTestSkipped('Candidate for deeper investigating YD-2892');
        $franchise = new Yourdelivery_Model_Servicetype_Franchise();

        $franchiseNames = array(
            'myFranchiseNAme',
            'myéäFranchise',
            'myéäFranchise Blub'
        );
        $franchiseIds = array();

        foreach ($franchiseNames as $index => $franchiseName) {
            $franchiseIds[$index] = $franchise->setFranchise($franchiseName);
        }

        $randomServices = array();
        foreach ($franchiseIds as $index => $id) {
            $randomServices[$index] = $this->getRandomService();
            $randomServices[$index]->setFranchiseTypeId($id);
            $randomServices[$index]->save();
        }

        foreach ($franchiseIds as $index => $id) {
            $franchiseName = str_replace(' ', '', iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $franchiseNames[$index]));
            $this->assertTrue($randomServices[$index]->{'is' . ucfirst($franchiseName)}(), sprintf("service #%d should be franchisetype #%d '%s', but isn't", $randomServices[$index]->getId(), $id, $franchiseName));
        }

        $db = $this->_getDbAdapter();
        foreach ($franchiseIds as $fId) {
            $db->query(sprintf('DELETE FROM `restaurant_franchisetype` WHERE `id` = %s', $fId));
        }
    }

}
