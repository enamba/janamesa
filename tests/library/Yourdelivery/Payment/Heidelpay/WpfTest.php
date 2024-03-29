<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Payment_Heidelpay_WpfTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.01.2012
     */
    public function testIsRefunded() {

        $orderId = $this->placeOrder(array('payment' => 'credit'));
        $order = new Yourdelivery_Model_Order($orderId);

        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => 'TRANSACTION.CHANNEL=d225a9fefe3fbaf400fe43294aca000d&TRANSACTION.MODE=CONNECTOR_TEST&REQUEST.VERSION=1.0&PAYMENT.CODE=CC.DB&ACCOUNT.COUNTRY=DE&FRONTEND.RESPONSE_URL=http%3A%2F%2Fstaging.lieferando.de%2Fpayment_heidelpay%2Fcallback%2Fsecret%2F91bc51c466ba869fda84148dd96c7f46904b28b5&FRONTEND.REDIRECT_TIME=0&FRONTEND.CSS_PATH=https%3A%2F%2Fcs.hosteurope.de%2Fyd-css%2Fyourdelivery-webpayment-de.css&PRESENTATION.AMOUNT=7.90&PRESENTATION.CURRENCY=EUR&PRESENTATION.USAGE=Bestellung-Nr.+e7apob1Q&IDENTIFICATION.TRANSACTIONID=464149-1320760333&FRONTEND.MODE=DEFAULT&FRONTEND.ENABLED=true&FRONTEND.POPUP=false&FRONTEND.LANGUAGE_SELECTOR=true&FRONTEND.LANGUAGE=de&NAME.GIVEN=test&NAME.FAMILY=tester&ADDRESS.STREET=testerstr+34&ADDRESS.ZIP=10117&ADDRESS.CITY=Berlin&ADDRESS.COUNTRY=DE&CONTACT.EMAIL=tester%40test.to&CONTACT.PHONE=018474050484045',
            'response' => 'REQUEST_VERSION=1.0&P3.VALIDATION=ACK&NAME.GIVEN=test&ADDRESS_STREET=testerstr+34&USER.LOGIN=d225a9fefe3fbaf400fe432757710009&ADDRESS_ZIP=10117&TRANSACTION_CHANNEL=d225a9fefe3fbaf400fe43294aca000d&ACCOUNT.COUNTRY=DE&PAYMENT_CODE=CC.DB&FRONTEND_RESPONSE_URL=http%3A%2F%2Fstaging.lieferando.de%2Fpayment_heidelpay%2Fcallback%2Fsecret%2F91bc51c466ba869fda84148dd96c7f46904b28b5&REQUEST.VERSION=1.0&CONTACT.PHONE=018474050484045&FRONTEND.LANGUAGE_SELECTOR=true&FRONTEND_LANGUAGE=de&TRANSACTION.CHANNEL=d225a9fefe3fbaf400fe43294aca000d&FRONTEND_CSS_PATH=https%3A%2F%2Fcs.hosteurope.de%2Fyd-css%2Fyourdelivery-webpayment-de.css&ADDRESS.CITY=Berlin&FRONTEND.REQUEST.CANCELLED=false&NAME_GIVEN=test&FRONTEND.MODE=DEFAULT&IDENTIFICATION_TRANSACTIONID=464149-1320760333&FRONTEND_ENABLED=true&USER_LOGIN=d225a9fefe3fbaf400fe432757710009&SECURITY.SENDER=d225a9fefe3fbaf400fe43281064000a&PRESENTATION_CURRENCY=EUR&FRONTEND.LANGUAGE=de&PROCESSING.RESULT=ACK&P3_VALIDATION=ACK&PRESENTATION.USAGE=Bestellung-Nr.+e7apob1Q&FRONTEND_REDIRECT_TIME=0&FRONTEND.CSS_PATH=https%3A%2F%2Fcs.hosteurope.de%2Fyd-css%2Fyourdelivery-webpayment-de.css&TRANSACTION_MODE=CONNECTOR_TEST&POST.VALIDATION=ACK&CONTACT.EMAIL=tester%40test.to&SECURITY_SENDER=d225a9fefe3fbaf400fe43281064000a&PRESENTATION.CURRENCY=EUR&TRANSACTION.SOURCE=WPF&USER.PWD=test&PROCESSING_RESULT=ACK&CONTACT_PHONE=018474050484045&PAYMENT.CODE=CC.DB&FRONTEND.ENABLED=true&NAME.FAMILY=tester&ADDRESS_COUNTRY=DE&TRANSACTION_SOURCE=WPF&PRESENTATION.AMOUNT=7.90&USER_PWD=test&IDENTIFICATION.TRANSACTIONID=464149-1320760333&FRONTEND.REDIRECT_TIME=0&FRONTEND.POPUP=false&FRONTEND_LANGUAGE_SELECTOR=true&PRESENTATION_USAGE=Bestellung-Nr.+e7apob1Q&NAME_FAMILY=tester&FRONTEND.RESPONSE_URL=http%3A%2F%2Fstaging.lieferando.de%2Fpayment_heidelpay%2Fcallback%2Fsecret%2F91bc51c466ba869fda84148dd96c7f46904b28b5&PRESENTATION_AMOUNT=7.90&CONTACT_EMAIL=tester%40test.to&TRANSACTION_RESPONSE=ASYNC&ADDRESS.COUNTRY=DE&POST_VALIDATION=ACK&TRANSACTION.MODE=CONNECTOR_TEST&ADDRESS_CITY=Berlin&ADDRESS.STREET=testerstr+34&TRANSACTION.RESPONSE=ASYNC&FRONTEND_MODE=DEFAULT&ACCOUNT_COUNTRY=DE&ADDRESS.ZIP=10117&FRONTEND_POPUP=false&FRONTEND_REQUEST_CANCELLED=false&FRONTEND.REDIRECT_URL=https%3A%2F%2Ftest-heidelpay.hpcgw.net%2Fsgw%2FhcoForm.jsp%3Bjsessionid%3DB0BBBDD17EDC8C3100F068E68EFE1A1B.worker11%3FFRONTENDLANGUAGESELECTED%3Dde',
        ))->save();
        
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => "",
            'response' => 'NAME_SALUTATION=MR&TRANSACTION_CHANNEL=d225a9fefe3fbaf400fe43294aca000d&IDENTIFICATION_UNIQUEID=31HA07BC8187040E66396F8187E81037&PROCESSING_REASON_CODE=00&PROCESSING_TIMESTAMP=2011-11-08+13%3A53%3A12&TRANSACTION_RESPONSE=SYNC&IDENTIFICATION_TRANSACTIONID=464149-1320760333&PROCESSING_STATUS_CODE=90&PROCESSING_RESULT=ACK&NAME_FAMILY=tester&PRESENTATION_USAGE=Bestellung-Nr.+e7apob1Q&CONTACT_EMAIL=tester%40test.to&CONTACT_PHONE=018474050484045&ADDRESS_ZIP=10117&PROCESSING_RETURN=Request+successfully+processed+in+%27Merchant+in+Connector+Test+Mode%27&PROCESSING_REASON=SUCCESSFULL&PRESENTATION_AMOUNT=7.90&CLEARING_DATE=2011-11-08+13%3A53%3A12&IDENTIFICATION_SHORTID=1846.8679.2800&CLEARING_AMOUNT=7.90&PROCESSING_CODE=CC.DB.90.00&P3_VALIDATION=ACK&ADDRESS_STATE=DE1&POST_VALIDATION=ACK&ACCOUNT_BRAND=VISA&ADDRESS_COUNTRY=DE&CLEARING_CURRENCY=EUR&PROCESSING_RETURN_CODE=000.100.112&ACCOUNT_MONTH=10&TRANSACTION_MODE=CONNECTOR_TEST&PAYMENT_CODE=CC.DB&ACCOUNT_NUMBER=411111%2A%2A%2A%2A%2A%2A1111&NAME_GIVEN=test&CLEARING_DESCRIPTOR=1846.8679.2800+Prenatal-Test+Bestellung-Nr.+e7apob1Q&ACCOUNT_HOLDER=test+tester&PROCESSING_STATUS=NEW&ADDRESS_CITY=Berlin&ADDRESS_STREET=testerstr+34&ACCOUNT_YEAR=2012&FRONTEND_REQUEST_CANCELLED=false&FRONTEND_RESPONSE_URL=https%3A%2F%2Ftest-heidelpay.hpcgw.net%2Fsgw%2Fpayment%2Fthreedsecure%3Bjsessionid%3DB0BBBDD17EDC8C3100F068E68EFE1A1B.worker11&PRESENTATION_CURRENCY=EUR&CLEARING_SUPPORT=%2B49+%280%29+6543210123&RESPONSE_VERSION=1.0',
        ))->save();

        $this->assertFalse($order->isRefunded());

        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => 'TRANSACTION.CHANNEL=d225a9fefe3fbaf400fe43294aca000d&TRANSACTION.MODE=CONNECTOR_TEST&TRANSACTION.RESPONSE=SYNC&IDENTIFICATION.TRANSACTIONID=464149&IDENTIFICATION.REFERENCEID=31HA07BC8187040E66396F8187E81037&REQUEST.VERSION=1.0&PAYMENT.CODE=CC.RF&PRESENTATION.AMOUNT=7.90&PRESENTATION.CURRENCY=EUR&PRESENTATION.USAGE=R%C3%BCckbuchung+Bestellung-Nr.+e7apob1Q',
            'response' => 'NAME.SALUTATION=MR&TRANSACTION.CHANNEL=d225a9fefe3fbaf400fe43294aca000d&IDENTIFICATION.UNIQUEID=31HA07BC8187040E663948AC2B596B40&PROCESSING_REASON_CODE=00&PROCESSING_TIMESTAMP=2011-11-08+13%3A55%3A47&TRANSACTION.RESPONSE=SYNC&IDENTIFICATION_TRANSACTIONID=464149&PROCESSING.STATUS.CODE=90&PROCESSING.RESULT=ACK&NAME_FAMILY=tester&PRESENTATION.USAGE=R%FCckbuchung+Bestellung-Nr.+e7apob1Q&CONTACT_EMAIL=tester%40test.to&CONTACT.PHONE=018474050484045&TRANSACTION_RESPONSE=SYNC&ADDRESS_ZIP=10117&PROCESSING.RETURN=Request+successfully+processed+in+%27Merchant+in+Connector+Test+Mode%27&PROCESSING.TIMESTAMP=2011-11-08+13%3A55%3A47&PROCESSING.REASON=SUCCESSFULL&PRESENTATION_AMOUNT=7.90&CLEARING.DATE=2011-11-08+13%3A55%3A47&IDENTIFICATION_SHORTID=1846.8694.7280&CLEARING.AMOUNT=7.90&PROCESSING_CODE=CC.RF.90.00&P3_VALIDATION=ACK&POST_VALIDATION=ACK&ADDRESS.STATE=DE1&ACCOUNT.BRAND=VISA&ADDRESS_COUNTRY=DE&CLEARING_CURRENCY=EUR&PROCESSING_RETURN_CODE=000.100.112&ACCOUNT_MONTH=10&TRANSACTION_MODE=CONNECTOR_TEST&ADDRESS.COUNTRY=DE&NAME_SALUTATION=MR&PROCESSING_RESULT=ACK&PAYMENT_CODE=CC.RF&CLEARING.CURRENCY=EUR&ACCOUNT.NUMBER=411111******1111&IDENTIFICATION.SHORTID=1846.8694.7280&NAME_GIVEN=test&CLEARING.DESCRIPTOR=1846.8694.7280+Prenatal-Test+R%FCckbuchung+Bestellung-Nr.+e7apob1Q&PROCESSING_RETURN=Request+successfully+processed+in+%27Merchant+in+Connector+Test+Mode%27&ACCOUNT.HOLDER=test+tester&PROCESSING_REASON=SUCCESSFULL&PROCESSING.STATUS=NEW&CLEARING_AMOUNT=7.90&PROCESSING.CODE=CC.RF.90.00&CONTACT.EMAIL=tester%40test.to&PROCESSING_STATUS_CODE=90&PROCESSING.REASON.CODE=00&ADDRESS_CITY=Berlin&TRANSACTION.MODE=CONNECTOR_TEST&PAYMENT.CODE=CC.RF&ADDRESS.STREET=testerstr+34&ACCOUNT_YEAR=2012&FRONTEND.REQUEST.CANCELLED=false&FRONTEND.RESPONSE_URL=https%3A%2F%2Ftest-heidelpay.hpcgw.net%2Fsgw%2Fpayment%2Fthreedsecure%3Bjsessionid%3DB0BBBDD17EDC8C3100F068E68EFE1A1B.worker11&ACCOUNT_NUMBER=411111******1111&CONTACT_PHONE=018474050484045&IDENTIFICATION.TRANSACTIONID=464149&ACCOUNT.MONTH=10&ACCOUNT_HOLDER=test+tester&CLEARING_DESCRIPTOR=1846.8694.7280+Prenatal-Test+R%FCckbuchung+Bestellung-Nr.+e7apob1Q&PRESENTATION_USAGE=R%FCckbuchung+Bestellung-Nr.+e7apob1Q&PROCESSING_STATUS=NEW&ADDRESS.CITY=Berlin&NAME.GIVEN=test&PRESENTATION_CURRENCY=EUR&PROCESSING.RETURN.CODE=000.100.112&ACCOUNT.YEAR=2012&FRONTEND_RESPONSE_URL=https%3A%2F%2Ftest-heidelpay.hpcgw.net%2Fsgw%2Fpayment%2Fthreedsecure%3Bjsessionid%3DB0BBBDD17EDC8C3100F068E68EFE1A1B.worker11&ADDRESS.ZIP=10117&ACCOUNT_BRAND=VISA&ADDRESS_STREET=testerstr+34&PRESENTATION.CURRENCY=EUR&NAME.FAMILY=tester&CLEARING_SUPPORT=%2B49+%280%29+6543210123&RESPONSE_VERSION=1.0&P3.VALIDATION=ACK&PRESENTATION.AMOUNT=7.90&POST.VALIDATION=ACK&CLEARING.SUPPORT=%2B49+%280%29+6543210123&IDENTIFICATION_UNIQUEID=31HA07BC8187040E663948AC2B596B40&RESPONSE.VERSION=1.0&TRANSACTION_CHANNEL=d225a9fefe3fbaf400fe43294aca000d&CLEARING_DATE=2011-11-08+13%3A55%3A47&ADDRESS_STATE=DE1&FRONTEND_REQUEST_CANCELLED=false',
        ))->save();
        
        $this->assertTrue($order->isRefunded());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 31.01.2012
     * @expectedException Yourdelivery_Payment_Heidelpay_Exception
     */
    public function testFailToRefund() {
        
        $orderId = $this->placeOrder(array('payment' => 'credit'));
        $order = new Yourdelivery_Model_Order($orderId);
        
        $heidelpay = new Yourdelivery_Payment_Heidelpay_Wpf();
        $heidelpay->refundOrder($order);
    }
    
}
