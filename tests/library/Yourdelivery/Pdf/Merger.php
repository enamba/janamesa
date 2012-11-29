<?php

class Merger extends Yourdelivery_Test{
    
    public function testMerge(){
        $pdf1 = APPLICATION_PATH_TESTING . '/../data/rechnung1.pdf';
        $pdf2 = APPLICATION_PATH_TESTING . '/../data/rechnung2.pdf';
        $this->assertTrue(file_exists($pdf1));
        $this->assertTrue(file_exists($pdf2));
        $merger = new Yourdelivery_Pdf_Merger();
        $this->assertTrue($merger->addPdf($pdf1));
        $this->assertTrue($merger->addPdf($pdf2));
        
        $mergedFile = $merger->merge();
        $this->assertTrue(file_exists($mergedFile));
    }
    
}

?>
