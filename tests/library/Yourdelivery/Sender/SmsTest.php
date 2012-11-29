<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 09.08.2011
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderSmsTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @author Daniel Scain <farenzena@lieferando.de>
     * @since 09.08.2011
     */
    public function testSend() {
        $sms = new Yourdelivery_Sender_Sms();
        $to = $this->config->testing->sender->sms->to;

        $this->assertTrue($sms->send($to, "Wurst"));
        $this->assertTrue($sms->send($to, "Wurst", 'lox24'));
        $this->assertTrue($sms->send($to, "Wurst", 'smstrade'));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @author Daniel Scain <farenzena@lieferando.de>
     * @since 02.01.2012
     */
    public function testSendKannel() {
        $to = $this->config->testing->sender->sms->toLocal;

        $smsKannel = new Yourdelivery_Sender_Sms_Kannel();
        $this->assertTrue($smsKannel->sendSmsMessage($to, "Wurst"));

        $sms = new Yourdelivery_Sender_Sms();
        $this->assertEquals($smsKannel->sendSmsMessage($to, "Wurst"), $sms->send($to, "Wurst", 'kannel'));
    }

    /**
     * SMSNewsMedia test case
     *
     * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 20.08.2012
     * 
     * SMSNewsMedia does not specify the encoding allowed to send messages and will output a
     * 'Received for processing' even if the characters on the message may not be processed.
     * To get further results from the processing we would need a asynchronous process to get
     * message sending status. For that matter, PHP process is not suitable anymore and
     * we need a system like RabbitMQ or even a simpler (but less maintanable) cronjob to update
     * the message status.
     * This final message status can be retrieved by a POST request.
     */
    public function testSendSMSNewsMedia() {
        // WARNING: this test method sends out real SMS messages
        // To avoid this behaviour, leave testing.sms value empty
        // NOTE: do not change message for each SMS - then in fact only one will be sent
        $msg = 'SMSNewsMedia test message ' . rand(0, 1000000);
        $to = $this->config->testing->sms;
        if (!strlen($to)) {
            $this->markTestSkipped('No testing.sms set in config');
        }

        // Preparing some variants of phone number
        $normalizedTo = Default_Helpers_Normalize::telephone($to);
        // 0048123456789
        $toIntl = $normalizedTo;
        // 123456789
        $toLocal = substr($normalizedTo, 4);
        // +481-234-567-89
        $toIntlFuzzy = '+' . implode('-', str_split(substr($normalizedTo, 2), 3));
        // 12 34 56 78 9
        $toLocalFuzzy = implode(' ', str_split($toLocal, 2));
        $smsService = new Yourdelivery_Sender_Sms_SmsNewsMedia();

        // Valid SMS set
        $this->assertTrue($smsService->sendSmsMessage($toIntl, $msg));
        $this->assertTrue($smsService->sendSmsMessage($toLocal, $msg));
        $this->assertTrue($smsService->sendSmsMessage($toIntlFuzzy, $msg));
        $this->assertTrue($smsService->sendSmsMessage($toLocalFuzzy, $msg));

        // Invalid SMS set
        $this->assertFalse($smsService->sendSmsMessage('', $msg));
        $this->assertFalse($smsService->sendSmsMessage('invalid-phone', $msg));
        $this->assertFalse($smsService->sendSmsMessage($toIntl, ''));
    }
}
