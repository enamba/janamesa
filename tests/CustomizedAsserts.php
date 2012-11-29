<?php

/**
 * this class is to customize some PHPUnit asserts
 * we can add extra functionality to the basic asserts and implement our own asserts
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 10.02.2012
 */
class Yourdelivery_Customized_Asserts extends Zend_Test_PHPUnit_ControllerTestCase {
    /**
     * Constants for Customized Asserts Failure Messages
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     */

    const MESSAGE_ARRAY_HAS_KEYS = "Failed to assert, that the array has all the keys.";

    /**
     * add additional information from doctype of failed test
     * and last logs
     *
     * @param string $message
     * @return string
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.02.2012
     */
    public static function prepareMessage($message, $includingPaymentLog = false, $includingSystemDebug = false) {
        return $message;


        if (strlen($message) <= 0) {
            $msg = array();
            try {
                $backtrace = debug_backtrace();
                $last = $backtrace[2];
                $r = new Zend_Reflection_Method($last['class'] . '::' . $last['function']);
                $msg[] = $r->getDocblock()->getShortDescription();
                foreach ($r->getDocblock()->getTags() as $tag) {
                    $msg[] .= $tag->getName() . ": " . $tag->getDescription();
                }
            } catch (Zend_Reflection_Exception $e) {
                
            }

            $message .= "\n================================================";
            $message .= "\n last log entries:";
            $message .= "\n------------------------------------------------";
            $message .= "\n" . Default_Helpers_Log::getLastLog('log', 60);

            if ($includingPaymentLog) {
                $message .= "\n================================================";
                $message .= "\n last payment log entries:";
                $message .= "\n------------------------------------------------";
                $message .= "\n" . Default_Helpers_Log::getLastLog('payment');
            }

            if ($includingSystemDebug) {
                $message .= "\n================================================";
                $message .= "\nSystem Debugging Info:\n";
                $cmdret = null;
                ob_start();
                // show open files
                passthru("lsof -w | wc -l", $cmdret);
                $ret = ob_get_contents();
                $message .= "\nOPEN FILES: " . $ret;
                ob_end_clean();

                $cmdret = null;
                ob_start();
                // show open files - hard limit
                passthru("ulimit -Hn", $cmdret);
                $ret = ob_get_contents();
                $message .= "OPEN FILES - Hard Limit: " . $ret;
                ob_end_clean();

                $cmdret = null;
                ob_start();
                // show open files - soft limit
                passthru("ulimit -Sn", $cmdret);
                $ret = ob_get_contents();
                $message .= "OPEN FILES - Soft Limit: " . $ret;
                ob_end_clean();
                $message .= "MEMORY USAGE: " . round(memory_get_usage() / 1012 / 1024, 2) . "MB\n";
                $message .= "MAX MEMORY USED: " . round(memory_get_peak_usage() / 1012 / 1024, 2) . "MB\n";
            }

            if (count($msg) > 0) {
                $message .= "\n================================================";
                $message .= "\nDOCTYPES OF FAILED TEST:\n" . implode("\n", $msg);
                $message .= "\n------------------------------------------------";
            }
        }
        return $message;
    }

    /**
     * Failure method for custom asserts.
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     * @param string $message
     */
    private function customizedFail($message) {
        if (strlen($message) > 0) {
            parent::fail(self::prepareMessage('') . "\n" . $message);
        } else {
            parent::fail(self::prepareMessage(''));
        }
    }

    public function assertRedirectTo($url, $message = '') {
        parent::assertRedirectTo($url, $this->prepareMessage($message));
    }

    public static function assertTrue($boolean, $message = '') {
        parent::assertTrue($boolean, self::prepareMessage($message));
    }

    public static function assertFalse($boolean, $message = '') {
        parent::assertFalse($boolean, self::prepareMessage($message));
    }

    public static function assertEquals($arg1, $arg2, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        parent::assertEquals($arg1, $arg2, self::prepareMessage($message), $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public static function assertNotEquals($arg1, $arg2, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        parent::assertNotEquals($arg1, $arg2, self::prepareMessage($message), $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public function assertController($controller, $message = '') {
        parent::assertController($controller, self::prepareMessage($message));
    }

    public function assertAction($action, $message = '') {
        parent::assertAction($action, self::prepareMessage($message));
    }

    public function assertResponseCode($code, $message = '') {
        parent::assertResponseCode($code, $this->prepareMessage($message));
    }

    public static function assertFileExists($file, $message = '') {
        parent::assertFileExists($file, self::prepareMessage($message));
    }

    public function assertRedirectRegex($regex, $message = '') {
        parent::assertRedirectRegex($regex, $this->prepareMessage($message));
    }

    public static function assertGreaterThan($arg1, $arg2, $message = '') {
        parent::assertGreaterThan($arg1, $arg2, self::prepareMessage($message));
    }

    public static function assertGreaterThanOrEqual($arg1, $arg2, $message = '') {
        parent::assertGreaterThanOrEqual($arg1, $arg2, self::prepareMessage($message));
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     * @param number $arg1
     * @param number $arg2
     * @param string $message
     */
    public static function assertLessThan($arg1, $arg2, $message = '') {
        parent::assertLessThan($arg1, $arg2, self::prepareMessage($message));
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     * @param number $arg1
     * @param number $arg2
     * @param string $message
     */
    public static function assertLessThanOrEqual($arg1, $arg2, $message = '') {
        parent::assertLessThanOrEqual($arg1, $arg2, self::prepareMessage($message));
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     * @param string $key
     * @param array $array
     * @param string $message
     */
    public static function assertArrayHasKey($key, array $array, $message = '') {
        parent::assertArrayHasKey($key, $array, self::prepareMessage($message));
    }

    /**
     * Asserts, that all the keys within the $keys array exist in $array
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 18.04.2012
     * @param array $keys
     * @param array $array
     */
    public static function assertArrayHasKeys($keys, $array, $message = '') {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                if (strlen($message) > 0) {
                    self::customizedFail($message);
                } else {
                    self::customizedFail(self::MESSAGE_ARRAY_HAS_KEYS);
                }
            }
        }
    }

    public static function assertIsPersistent($model) {
        parent::assertTrue($model->isPersistent());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     * 
     * @param type $array 
     */
    public static function assertIsArray($array) {
        parent::assertTrue(is_array($array), sprintf('Failed asserting that %s is array', print_r($array)));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    private static function logSkippedIncomplete($message = null) {
        $file = fopen(APPLICATION_PATH ."/../tests/incompleteSkippedTests.txt", 'a+');
        $backtrace = debug_backtrace();
        $message = date('Y-m-d H:i:s') . " : " . $backtrace[2]['class'] . '::' . $backtrace[2]['function'] . ": this testcase was skipped / marked incomplete with message '$message'\n";
        fwrite($file, $message);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public static function markTestSkipped($message = null) {
        self::logSkippedIncomplete($message);
        parent::markTestSkipped($message);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public static function markTestIncomplete($message = null) {
        self::logSkippedIncomplete($message);
        parent::markTestIncomplete($message);
    }

}
