<?php

/**
 * @author mlaug
 */
class FileTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 28.10.2010
     */
    public function testFilename() {
        $file = '/path/to/file/samson.csv';
        $this->assertEquals(Default_Helpers_File::ShowFileName($file), 'samson.csv');
    }

    public function testFileExtension() {
        $file = '/path/to/file/samson.csv';
        $this->assertEquals('csv', Default_Helpers_File::getFileExtension($file));
        $file = '/path/to/file/rep-order-378384-3478347634-lieferando.de.xml';
        $this->assertEquals('xml', Default_Helpers_File::getFileExtension($file));
    }

}
