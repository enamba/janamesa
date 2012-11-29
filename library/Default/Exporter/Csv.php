<?php

/**
 * Create a csv file from given data and structure
 * may be converted to excel after creation
 * @package export
 * @author mlaug
 */
class Default_Exporter_Csv {

    /**
     * storage object to save files
     * @var Default_File_Storage
     */
    protected $_storage = null;
    /**
     * stores all the rows
     * @var array
     */
    protected $_rows = array();
    /**
     * stores all the cols
     * @var array
     */
    protected $_cols = array();
    /**
     * path to csv file
     * @var string
     */
    public $csv = null;
    /**
     * path to xsl file
     * @var string
     */
    public $xsl = null;
    /**
     * filename
     * @var string
     */
    public $filename = null;
    
    /**
     * seperator for this csv file
     * @var string 
     */
    public $seperator = ";";
    
    /**
     * decode UTF8?
     * @var bool 
     */
    public $decodeUtf8 = true;
    
    /**
     * show header or not
     * @var boolean 
     */
    public $header = true;

    /**
     * extension of output file
     * @var string 
     */
    public $extension = '.csv';
    
    /**
     * @author mlaug
     * @since 13.08.2010
     */
    public function __construct($decodeUtf8 = true) {
        $this->_storage = new Default_File_Storage();
        $this->_storage->setSubFolder('csvexport');
        $this->_storage->setTimeStampFolder();
        $this->decodeUtf8 = $decodeUtf8;
    }

    /**
     * add a column to the export
     * @author mlaug
     * @param string $col
     */
    public function addCol($col = null) {
        //if a column has been added, we are not allowed to add more rows
        if (!is_null($col) && !empty($col)) {
            if (!in_array($col, $this->_cols)) {
                $this->_cols[] = $this->decodeUtf8 ? utf8_decode($col) : $col;
            }
        }
    }

    /**
     * add cols as array
     * @author mlaug
     * @since 22.10.2010
     * @param array $cols
     */
    public function addCols(array $cols) {
        foreach ($cols as $col) {
            $this->addCol($col);
        }
    }

    /**
     * add a row
     * @author mlaug
     * @since 22.10.2010
     * @param array $col
     */
    public function addRow(array $row) {
        if (is_array($row) && count($this->_cols) == count($row)) {
            $item = $this->decodeUtf8 ? utf8_decode($item) : $item;
            $this->_rows[] = array_map(function($item){ return $item; },$row);
        }
    }

    /**
     * @author mlaug
     * @since 22.10.2010
     * @param array $rows
     */
    public function addRows(array $rows) {
        foreach ($rows as $row) {
            if (is_array($row)) {
                $this->addRow($row);
            }
        }
    }

    /**
     * saves the csv data to file or provide as download
     * @author mlaug
     * @since 03.05.2011
     * @return string|Zend_Controller_Response_Abstract
     */
    public function save(Zend_Controller_Response_Abstract $response = null) {

        $header = '';
        if ( $this->header ){
            $header = implode($this->seperator, $this->_cols) . "\r\n";
        }
        
        $content = "";
        foreach ($this->_rows as $row) {
            $content .= implode($this->seperator, $row) . "\r\n";
        }
        $compl = $header . $content;
        if (is_null($this->filename)) {
            $this->filename = 'export-' . time() . $this->extension;
        }
        $this->_storage->store($this->filename, $compl);
        $this->csv = $this->_storage->getCurrentFolder() . '/' . $this->filename;

        if ($response instanceof Zend_Controller_Response_Abstract) {
            $response->setHttpResponseCode(200);
            $response->setHeader('Content-Type', 'application/csv-tab-delimited-table')
                     ->setHeader('Content-Disposition', 'attachment; filename="' . $this->filename . '"')
                     ->setHeader('Content-Transfer-Encoding', 'binary')
                     ->setHeader('Expires', '0')
                     ->setHeader('Pragma', 'no-cache');
            readfile($this->csv);           
            return $response;
        }

        return $this->csv;
    }

    /**
     * @author mlaug
     * @since 13.08.2010
     * @return string
     */
    public function convert2xsl() {
        if (file_exists($this->csv)) {
            /**
             * @todo: implement convertion to excell sheet
             */
        }
        return null;
    }

    /**
     * @author mlaug
     * @since 22.10.2010
     */
    public function provideDownload() {
        
    }

}

?>
