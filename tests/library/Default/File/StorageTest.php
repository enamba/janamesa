<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object); else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * Description of Storage
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class StorageTest extends Yourdelivery_Test {

    /**
     * @return Default_File_Storage 
     */
    private function getStorage() {
        rrmdir(APPLICATION_PATH . '/../storage/testcase');
        $storage = new Default_File_Storage();
        $storage->setSubFolder('testcase');
        return $storage;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testInitStorage() {
        $storage = new Default_File_Storage();
        $this->assertTrue(file_exists($storage->getCurrentFolder()));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testAddAndRemoveFile() {
        $testfile = APPLICATION_PATH_TESTING . '/../data/storage/testfile.txt';
        $storage = $this->getStorage();
        $this->assertTrue(file_exists($storage->getCurrentFolder()));
        $fp = $storage->store('samson.txt', file_get_contents($testfile));
        $this->assertTrue(file_exists($storage->getCurrentFolder() . '/' . $fp->getFilename()));
        $storage->delete('samson.txt');
        $this->assertFalse(file_exists($storage->getCurrentFolder() . '/' . $fp->getFilename()));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testFirstSecondLetterCreator() {
        $storage = $this->getStorage();
        $folder = $storage->getCurrentFolder();
        $storage->setLetterFolder('samson', 1);
        $this->assertTrue(file_exists($folder . '/s'));
        $storage->resetSubFolder();
        $storage->setSubFolder('testcase');
        $storage->setLetterFolder('samson', 2);
        $this->assertTrue(file_exists($folder . '/s/a'));
        $storage->resetSubFolder();
        $storage->setSubFolder('testcase');
        $storage->setLetterFolder('samson', 3);
        $this->assertTrue(file_exists($folder . '/s/a/m'));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testTimestampFolder() {
        $storage = $this->getStorage();
        $folder = $storage->getCurrentFolder();
        $storage->setTimeStampFolder();
        $this->assertTrue(file_exists($folder . '/' . date('d-m-Y')));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testResetFolder() {
        $storage = $this->getStorage();
        $storage->resetSubFolder();
        $this->assertEquals($storage->getCurrentFolder(), APPLICATION_PATH . '/../storage/');
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testFileExists(){
        $testfile = APPLICATION_PATH_TESTING . '/../data/storage/testfile.txt';
        $storage = $this->getStorage();
        $this->assertFalse($storage->exists('samson.txt'));
        $storage->store('samson.txt', file_get_contents($testfile));
        $this->assertTrue($storage->exists('samson.txt'));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testLs(){
        $testfile = APPLICATION_PATH_TESTING . '/../data/storage/testfile.txt';
        $storage = $this->getStorage();
        $this->assertEquals(0,count($storage->ls()));
        $storage->store('samson.txt', file_get_contents($testfile));
        $this->assertEquals(1,count($storage->ls()));
        $storage->store('samson2.txt', file_get_contents($testfile));
        $this->assertEquals(2,count($storage->ls()));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testCopy(){
        $testfile = APPLICATION_PATH_TESTING . '/../data/storage/testfile.txt';
        $storage = $this->getStorage();
        $fp = $storage->store('samson.txt', file_get_contents($testfile));
        $this->assertTrue($storage->copy('samson.txt','/done/samson.txt'));
        $this->assertTrue(file_exists($storage->getCurrentFolder() . '/' . $fp->getFilename()));
        $this->assertTrue(file_exists($storage->getCurrentFolder() . '/done/' . $fp->getFilename()));
        $this->assertFalse($storage->copy('missingfile.txt','anywhereelse/missing.txt'));      
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testMove(){
        $testfile = APPLICATION_PATH_TESTING . '/../data/storage/testfile.txt';
        $storage = $this->getStorage();
        $fp = $storage->store('samson.txt', file_get_contents($testfile));
        $this->assertTrue($storage->move('samson.txt','/done/samson.txt'));
        $this->assertFalse(file_exists($storage->getCurrentFolder() . '/' . $fp->getFilename()));
        $this->assertTrue(file_exists($storage->getCurrentFolder() . '/done/' . $fp->getFilename()));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testOrderStorage(){
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertTrue(file_exists(APPLICATION_PATH . '/../storage/orders/' . date('d-m-Y') . '/' . $order->getId() . '-ordersheet-restaurant.pdf'));
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.01.2012 
     */
    public function testServiceStorage(){
        $service = $this->getRandomService();
        $service->getStorage();
        $this->assertTrue(file_exists(APPLICATION_PATH . '/../storage/restaurants/' . $service->getId()));
    }

}

?>
