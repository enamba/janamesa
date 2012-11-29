<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Migrate
 *
 * @author mlaug
 */
class Yourdelivery_Model_Billing_Migrate extends Yourdelivery_Model_Billing {
    /**
     * get all files associated with this bill
     * @author mlaug
     * @since 05.06.2011
     * @return array
     */
    public function getAllFiles() {
        return array_merge(
            $this->getAllPdfFiles(), $this->getAllCsvFiles()
        );
    }

    /**
     * get all pdf files
     * @author mlaug
     * @since 05.06.2011
     */
    public function getAllPdfFiles() {
        $pdfs = array();
        $pdfs[] = $this->getPdf();
        $pdfs = array_merge($pdfs, $this->getAdditionalFiles(true, false));
        return array_filter($pdfs,function($f){ return file_exists($f); });
    }

    /**
     * get all csv files
     * @author mlaug
     * @since 05.06.2011
     * @return array
     */
    public function getAllCsvFiles() {
        $csvs = array();
        $csvs[] = $this->getCsv();
        $csvs = array_merge($csvs, $this->getAdditionalFiles(false, true));
        return array_filter($csvs,function($f){ return file_exists($f); });
    }
    
    /**
     * Get pdf of invoice
     * @author vpriem
     * @since 01.08.2010
     * @return mixed
     */
    public final function getPdf() {

        //check for overwrite (sent files)
        if (isset($this->_data['pdf'])) {
            return $this->_data['pdf'];
        }

        $sheet = APPLICATION_PATH . '/../storage/billing/' . $this->getNumber() . '.pdf';
        if (file_exists($sheet)) {
            return $sheet;
        }
        return false;
    }

    /**
     * get one explicit sub pdf
     * @author vpriem
     * @since 09.03.2013
     * @param int $id costcenterid
     * @return mixed
     */
    public final function getSubPdf($id) {

        $sheet = APPLICATION_PATH . '/../storage/billing/' . $this->getNumber() . '/' . $this->getNumber() . '-' . ((integer) $id) . '.pdf';
        if (file_exists($sheet)) {
            return $sheet;
        }
        return false;
    }

    /**
     * Get additional pdf of invoice
     * @author vpriem
     * @since 12.08.2010
     * @return array
     */
    public final function getAdditionalFiles($pdf = true, $csv = true) {

        $sheets = array();

        $dirname = APPLICATION_PATH . '/../storage/billing/' . $this->getNumber();
        if (is_dir($dirname)) {
            $files = scandir($dirname);
            foreach ($files as $file) {
                if ($pdf && preg_match("`\.pdf$`", $file)) {
                    $sheets[] = $dirname . "/" . $file;
                }
                if ($csv && preg_match("`\.csv$`", $file)) {
                    $sheets[] = $dirname . "/" . $file;
                }
            }
        }

        return $sheets;
    }

    /**
     * Get pdf of asset
     * @author mlaug
     * @since 11.03.2011
     * @return mixed
     */
    public final function getAssetPdf() {
        $sheet = str_replace('R', 'A', APPLICATION_PATH . '/../storage/billing/' . $this->getNumber() . '.pdf');
        if (file_exists($sheet)) {
            return $sheet;
        }
        return false;
    }

    /**
     * Get pdf of voucher
     * @author mlaug
     * @since 01.08.2010
     * @return mixed
     */
    public final function getVoucherPdf() {

        $sheet = str_replace('R', 'G', APPLICATION_PATH . '/../storage/billing/' . $this->getNumber() . '.pdf');
        if (file_exists($sheet)) {
            return $sheet;
        }
        return false;
    }

    /**
     * Get csv of invoice
     * @author vpriem
     * @since 01.08.2010
     * @return mixed
     */
    public final function getCsv() {

        $sheet = APPLICATION_PATH . '/../storage/billing/' . $this->getNumber() . '.csv';
        if (file_exists($sheet)) {
            return $sheet;
        }
        return false;
    }
}

?>
