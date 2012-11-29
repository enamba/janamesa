<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Merger
 *
 * @author matthiaslaug
 */
class Yourdelivery_Pdf_Merger {

    protected $_pdfs = array();

    public function addPdf($pdf) {
        if (file_exists($pdf)) {
            $this->_pdfs[] = $pdf;
            return true;
        }
        return false;
    }

    public function merge() {

        if (count($this->_pdfs) == 0) {
            return null;
        }

        // Array of the pdf files need to be merged
        $pdfNew = new Zend_Pdf();
        foreach ($this->_pdfs as $file) {
            $pdf = Zend_Pdf::load($file);
            $extractor = new Zend_Pdf_Resource_Extractor();
            foreach ($pdf->pages as $page) {
                $pdfExtract = $extractor->clonePage($page);
                $pdfNew->pages[] = $pdfExtract;
            }
        }

        //get unique number and remove dots
        $randomNumber = str_replace('.', '', uniqid('', true));

        $mergePdf = '/tmp/' . $randomNumber . "-mergefile.pdf";
        $pdfNew->save($mergePdf);
        return $mergePdf;
    }

}

?>
