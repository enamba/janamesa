<?php

/**
 * @author vpriem
 * @since 02.11.2010
 */
/**
 * @runTestsInSeparateProcesses 
 */
class DbTableLinkTest extends Yourdelivery_Test {

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testFindByUrl() {

        $dbTable = new Yourdelivery_Model_DbTable_Link();
        $link = $dbTable->findRow(1);
        $this->assertTrue($link instanceof Yourdelivery_Model_DbTableRow_Link);
        $link = $dbTable->findByUrl($link->domain, $link->url);
        $this->assertTrue($link instanceof Yourdelivery_Model_DbTableRow_Link);
    }

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testPublish() {

        $dbTable = new Yourdelivery_Model_DbTable_Link();
        $link = $dbTable->findRow(1);
        $this->assertTrue($link instanceof Yourdelivery_Model_DbTableRow_Link);
        @unlink(APPLICATION_PATH . "/../storage/public/" . $link->getAbsoluteUrl() . ".html");
        $link->publish();
        $this->assertFileExists(APPLICATION_PATH . "/../storage/public/" . $link->getAbsoluteUrl() . ".html");
        @unlink(APPLICATION_PATH . "/../storage/public/" . $link->getAbsoluteUrl() . ".html");
    }

}
