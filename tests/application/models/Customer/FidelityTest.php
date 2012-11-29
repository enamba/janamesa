<?php

/**
 * @runTestsInSeparateProcesses
 */
class CustomerFidelityTest extends Yourdelivery_Test {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.11.2011
     */
    public function testAddAndStornoPoints() {
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isLoggedIn());
        $fidelity = $customer->getFidelity();
        $this->assertTrue($fidelity instanceof Yourdelivery_Model_Customer_Fidelity);
        $points = $fidelity->getPoints();
        $fidelity->addTransaction('manual', 'spass haben', 12);
        $this->assertGreaterThan(0, $fidelity->getLastTransactionId());
        $this->assertEquals($points + 12, $fidelity->getPoints());
        $fidelity->modifyTransaction($fidelity->getLastTransactionId(), -1);
        $this->assertEquals($points, $fidelity->getPoints());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 27.11.2011
     */
    public function testUniqueActions() {
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isLoggedIn());
        $fidelity = $customer->getFidelity();
        $this->assertTrue($fidelity instanceof Yourdelivery_Model_Customer_Fidelity);
        $points = $fidelity->getPoints();
        $uniques = $fidelity->getUniqueActions();
        foreach ($uniques as $unique) {
            //storno action if already available
            $oldOnes = $fidelity->getTransactions($unique);
            foreach ($oldOnes as $old) {
                $points = $fidelity->modifyTransaction($old['id'], -1);
            }

            $n_points = $fidelity->addTransaction($unique, 'samson tiffy');
            $this->assertNotEquals($points, $n_points);
            $this->assertFalse($fidelity->isOldTransaction());

            //next time we want to add, we just get the same points back, no action triggered
            $m_points = $fidelity->addTransaction($unique, 'samson tiffy');
            $this->assertEquals($n_points, $m_points);
            $this->assertTrue($fidelity->isOldTransaction());
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPointsUntilTransaction() {
        $cust = $this->getRandomCustomer();
        $table = $cust->getFidelity()->getTable();
        $table->delete($table->getAdapter()->quoteInto("email = ?", $cust->getEmail()));


        // create some transactions
        $firstTransactionId = $table->insert(array(
            'email' => $cust->getEmail(),
            'created' => date('Y-m-d H:i:s', strtotime('yesterday 1pm')),
            'transactionData' => 'blub1',
            'status' => 0,
            'action' => 'testPointsUntilTransaction',
            'points' => 12
                )
        );

        $secondTransactionId = $table->insert(array(
            'email' => $cust->getEmail(),
            'created' => date('Y-m-d H:i:s', strtotime('yesterday 6pm')),
            'transactionData' => 'blub2',
            'status' => 0,
            'action' => 'testPointsUntilTransaction',
            'points' => 15
                )
        );
        $this->assertEquals(27, $cust->getFidelity()->getPoints());

        $firstTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($firstTransactionId);
        $this->assertEquals(0, $firstTransaction->getPointsUntil());

        $secondTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($secondTransactionId);
        $this->assertEquals(12, $secondTransaction->getPointsUntil());

        $thirdTransactionId = $table->insert(array(
            'email' => $cust->getEmail(),
            'created' => date('Y-m-d H:i:s', strtotime('yesterday 9pm')),
            'transactionData' => 'blub3',
            'status' => 0,
            'action' => 'testPointsUntilTransaction',
            'points' => 3
                )
        );

        $secondTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($secondTransactionId);
        $this->assertEquals(12, $secondTransaction->getPointsUntil());

        $thirdTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($thirdTransactionId);
        $this->assertEquals(27, $thirdTransaction->getPointsUntil());

        // storno second transaction
        $secondTransaction->setData(array('status' => -1))->save();

        $cust = new Yourdelivery_Model_Customer($cust->getId());
        $cust->clearCache();
        $this->assertEquals(15, $cust->getFidelity()->getPoints(), $cust->getEmail());

        $thirdTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($thirdTransactionId);
        $this->assertEquals(12, $thirdTransaction->getPointsUntil());
        $secondTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($secondTransactionId);
        $this->assertEquals(12, $secondTransaction->getPointsUntil());

        $firstTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($firstTransactionId);
        $this->assertEquals(0, $firstTransaction->getPointsUntil());

        //confirm
        $secondTransaction->setData(array('status' => 0))->save();
        $thirdTransaction = new Yourdelivery_Model_Customer_FidelityTransaction($thirdTransactionId);
        $this->assertEquals(27, $thirdTransaction->getPointsUntil());
    }

    /**
     * empty email should fail migration
     *
     * @author Matthias Laug <laug@lieferando.de>, Andre Ponert <ponert@lieferando.de>
     * @since 03.08.2012
     */
    public function testMigrateInvalidEmail(){
        $customer = $this->getRandomCustomer();
        $this->assertFalse($customer->getFidelity()->migrateToEmail());
        $this->assertFalse($customer->getFidelity()->migrateToEmail(null));
        $this->assertFalse($customer->getFidelity()->migrateToEmail(''));
        $this->assertFalse($customer->getFidelity()->migrateToEmail('abcTeststring'));
        $this->assertFalse($customer->getFidelity()->migrateToEmail('adresse@unvollstaendig'));
        $this->assertFalse($customer->getFidelity()->migrateToEmail('@bla'));
        $this->assertFalse($customer->getFidelity()->migrateToEmail('bla@'));
        $this->assertFalse($customer->getFidelity()->migrateToEmail('name@lieferando.gibtesnicht'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.04.2012
     */
    public function testMigrateEmail() {
        $customer = $this->getRandomCustomer();

        $points = $customer->getFidelity()->getPoints();

        $customer->clearCache();
        $this->assertEquals($points, $customer->getFidelity()->getPoints());


        // test different email
        $randomEmail = $customer->getId() . '-' . Default_Helper::generateRandomString(6) . '@testcase.de';
        $this->assertTrue($customer->getFidelity()->migrateToEmail($randomEmail));

        $customer->setEmail($randomEmail);
        $customer->save();

        $checkCustomer = new Yourdelivery_Model_Customer($customer->getId());
        $this->assertEquals($randomEmail, $checkCustomer->getEmail());
        $this->assertEquals($points, $checkCustomer->getFidelity()->getPoints());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.07.2012
     */
    public function testOpenAction() {
        $db = $this->_getDbAdapter();
        $select = $db->select()->from(array('c' => 'customers'), 'c.id')
                        ->join(array('cft' => 'customer_fidelity_transaction'), 'cft.email = c.email')
                        ->where('c.profileImage IS NOT NULL')
                        ->order('RAND()')->limit(1);

        $customer = new Yourdelivery_Model_Customer($db->fetchOne($select));

        $cftTable = new Yourdelivery_Model_DbTable_Customer_FidelityTransaction();
        $cftTable->delete(sprintf('action = "accountimage" AND email = "%s"', $customer->getEmail()));
        $customer->clearCache();

        $openActions = $customer->getFidelity()->getOpenActions();
        $this->assertFalse(
                array_key_exists('ac', $openActions), sprintf(
                        'open action acount image was found for customer #%d %s although customer has uploaded profile image already', $customer->getId(), $customer->getEmail()
                )
        );
    }

}