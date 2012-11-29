<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 20.02.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Yourdelivery_Model_DbTable_Ebanking_TransactionsTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     */
    public function testPayerId() {

        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Transactions();
        $dbRow = $dbTable->createRow(array(
            'orderId' => 123,
            'data' => 'a:30:{s:11:"transaction";s:25:"34820-98787-4E8F352C-C349";s:7:"user_id";s:5:"34820";s:10:"project_id";s:5:"98787";s:13:"sender_holder";s:14:"Hochheim Janny";s:21:"sender_account_number";s:10:"60XXXXXX03";s:16:"sender_bank_code";s:8:"100XXXXX";s:16:"sender_bank_name";s:15:"LandesbankXXXXX";s:15:"sender_bank_bic";s:11:"BELAXXXXXXX";s:11:"sender_iban";s:22:"DE14100XXXXXXXXXXXXX03";s:17:"sender_country_id";s:2:"DE";s:16:"recipient_holder";s:21:"yd. yourdelivery GmbH";s:24:"recipient_account_number";s:9:"11XXXXX02";s:19:"recipient_bank_code";s:8:"100XXXXX";s:19:"recipient_bank_name";s:15:"Deutsche BXXXXX";s:18:"recipient_bank_bic";s:11:"DEUTXXXXXXX";s:14:"recipient_iban";s:22:"DE68100XXXXXXXXXXXXX02";s:20:"recipient_country_id";s:2:"DE";s:25:"international_transaction";s:1:"0";s:6:"amount";s:5:"16.80";s:11:"currency_id";s:3:"EUR";s:8:"reason_1";s:23:"Bestellung-Nr. 8Jsj7CFV";s:8:"reason_2";s:0:"";s:17:"security_criteria";s:1:"1";s:15:"user_variable_0";s:6:"393574";s:15:"user_variable_1";s:41:"www.lieferando.de/payment_ebanking/finish";s:15:"user_variable_2";s:41:"www.lieferando.de/payment_ebanking/cancel";s:15:"user_variable_3";s:41:"www.lieferando.de/payment_ebanking/notify";s:15:"user_variable_4";s:0:"";s:15:"user_variable_5";s:0:"";s:7:"created";s:19:"2011-10-07 19:22:31";}',
        ));
        
        $this->assertEquals($dbRow->generatePayerId(), "e18aca758705dbe9975b7c0275f0bcda8e724aec");
        $dbRow->save();
        $this->assertEquals($dbRow->payerId, "e18aca758705dbe9975b7c0275f0bcda8e724aec");
    }

}
