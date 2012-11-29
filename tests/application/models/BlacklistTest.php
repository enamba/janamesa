<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 12.06.2012 
 */

/**
 * @runTestsInSeparateProcesses 
 */
class BlacklistTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.08.2012
     * @expectedException Yourdelivery_Exception_Database_Inconsistency
     */
    public function testConstructInexistantValue() {

        $email = uniqid() . "@lieferando.de";

        new Yourdelivery_Model_Support_Blacklist_Values(null, 'email', $email);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.08.2012
     */
    public function testConstructExistantValue() {

        $email = uniqid() . "@lieferando.de";

        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value1 = $blacklist->addValue('email', $email);
        $this->assertGreaterThan(0, $blacklist->save());
        
        $value2 = new Yourdelivery_Model_Support_Blacklist_Values(null, 'email', $email);
        $this->assertEquals($value1->getId(), $value2->getId());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012 
     */
    public function testStoreValue() {

        $email = uniqid() . "@lieferando.de";

        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $blacklist->addValue('email', $email);
        $this->assertEquals(count($blacklist->getValues()), 1);
        $blacklist->addValue('keyword', uniqid());
        $this->assertEquals(count($blacklist->getValues()), 2);
        $blacklistId = $blacklist->save();
        $this->assertGreaterThan(0, $blacklistId);

        $this->assertEquals($blacklist->getValue('404'), null);

        $value = $blacklist->getValue('email');
        $this->assertTrue($value instanceof Yourdelivery_Model_Support_Blacklist_Values);
        $this->assertEquals($value->getType(), 'email');
        $this->assertEquals($value->getValue(), $email);
        $this->assertEquals($value->getMatching(), Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT);
        $this->assertEquals($value->getBehaviour(), Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_FAKE);

        $blacklist = new Yourdelivery_Model_Support_Blacklist($blacklistId);
        $this->assertEquals(count($blacklist->getValues()), 2);
        $value = $blacklist->getValue('email');
        $this->assertTrue($value instanceof Yourdelivery_Model_Support_Blacklist_Values);
        $this->assertEquals($value->getType(), 'email');
        $this->assertEquals($value->getValue(), $email);

        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $blacklist->addValue('email', $email);
        $this->assertFalse($blacklist->save());
        $value->setDeleted(1);
        $this->assertTrue($value->save());
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $blacklist->addValue('email', $email);
        $this->assertGreaterThan(0, $blacklist->save());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.08.2012
     */
    public function testRestoreValue() {

        $value1 = new Yourdelivery_Model_Support_Blacklist_Values();
        $this->assertFalse($value1->restore());

        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value2 = $blacklist->addValue('email', uniqid());
        $this->assertGreaterThan(0, $blacklist->save());
        $this->assertFalse($value2->restore());
        $value2->setDeleted(1);
        $this->assertTrue($value2->save());
        $this->assertTrue($value2->restore());
        $this->assertEquals($value2->getDeleted(), 0);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012 
     */
    public function testGetList() {

        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL, uniqid());
        $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP, uniqid());
        $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID, uniqid());
        $this->assertGreaterThan(0, $blacklist->save());

        $listEmail = Yourdelivery_Model_Support_Blacklist::getList(array('email'));
        $this->assertGreaterThan(0, count($listEmail));
        $listKeyword = Yourdelivery_Model_Support_Blacklist::getList(array('keyword'));
        $this->assertGreaterThan(0, count($listKeyword));
        $listPaypal = Yourdelivery_Model_Support_Blacklist::getList(array('paypal'));
        $this->assertGreaterThan(0, count($listPaypal));
        $listAll = Yourdelivery_Model_Support_Blacklist::getList(array('email', 'keyword', 'paypal'));
        $this->assertGreaterThan(0, count($listAll));
        $this->assertEquals(count($listAll), count($listEmail) + count($listKeyword) + count($listPaypal));
    }

}
