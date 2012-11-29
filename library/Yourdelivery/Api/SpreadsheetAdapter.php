<?php
/**
 * Google Spreadsheets adapter
 * @author vait
 */
class Yourdelivery_Api_SpreadsheetAdapter {

    /**
     * @var Zend_Gdata_Spreadsheets
     */
    protected $spreadsheetService;

    protected $spreadsheetId;

    protected $worksheet;

    protected $listFeed;

    /**
     * construct google spreadsheet adapter
     * @param string $gmailAccount e.g user@googlemail.com
     * @param string $password
     * @param string $spreadsheetId can be found in gmail URL
     * @return
     */
    public function __construct($gmailAccount, $password, $spreadsheetId, $worksheet = "default") {
        $client = Zend_Gdata_ClientLogin::getHttpClient($gmailAccount, $password, Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME);

        $config  = array('timeout' => 240);
        $client->setConfig($config);

        $this->spreadsheetService = new Zend_Gdata_Spreadsheets($client);
        $this->spreadsheetId = $spreadsheetId;
        $this->worksheet = $worksheet;
    }

    /**
     * get data rows from the table
     * @param int $worksheet worksheet id
     * @return array
     */
    public function getRows($worksheet = 0) {
        $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);
        $feed = $this->spreadsheetService->getWorksheetFeed($query);
        return $feed->entries[$worksheet]->getContentsAsRows();
    }

    /**
     * get column names from the table
     * @return array
     */
    public function getColumns() {
        $query = new Zend_Gdata_Spreadsheets_CellQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);
        $feed = $currentWorksheet = $this->spreadsheetService->getCellFeed($query);
        $columns = array();
        $columnCount = intval($feed->getColumnCount()->getText());

        for($i = 0; $i < $columnCount; $i++)
        {
            $entry = $feed->getEntry();                
            if ($entry [$i]) {
                if ($entry[$i]->getCell()->getRow() > 1) break;
                $columns[$i] = $entry[$i]->getCell()->getText();
            }
        }

        return $columns;
    }
    
    /**
     * remove row
     * @param int $index row to be removed
     * @return
     */
    public function removeRow($index) {
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);
        //$query->setWorksheetId($this->currWkshtId);
        $this->listFeed = $this->spreadsheetService->getListFeed($query);

        if($index >= $this->listFeed->count()){
            return;
        }

        $this->spreadsheetService->deleteRow($this->listFeed->entries[$index]);
    }

    /**
     * inserts row at the end of the table
     * @param array $payload data to be inserted in form: array('columnname1' = val1, 'columnname2' = val ... )
     * @param int $worksheet worksheet id
     * @return
     */
    public function insertRow($payload, $worksheetId = 0) {
        if ($worksheetId != 0) {
            $wid = $this->getWorksheetId($worksheetId);
            return $this->spreadsheetService->insertRow($payload, $this->spreadsheetId, $wid);
        }

        return $this->spreadsheetService->insertRow($payload, $this->spreadsheetId);
    }


    /**
     * update row
     * @param int $index row to be updated
     * @param array $payload data to be updated in form: array('columnname1' = val1, 'columnname2' = val ... )
     * @param int $worksheet worksheet id
     * @return
     */
    public function updateRow($index, $payload, $worksheetId = 0) {
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);

        if ($worksheetId != 0) {
            $wid = $this->getWorksheetId($worksheetId);
            $query->setWorksheetId($wid);
        }

        $this->listFeed = $this->spreadsheetService->getListFeed($query);

        if($index >= $this->listFeed->count()){
            return;
        }

        $entry = $this->listFeed->entries[$index];
        
        return $this->spreadsheetService->updateRow($entry, $payload);
    }


    /**
     * get worksheet id on corresponding index
     * @param int $index 
     * @return worksheet id
     */
    private function getWorksheetId($index) {
        $subquery = new Zend_Gdata_Spreadsheets_DocumentQuery();
        $subquery->setSpreadsheetKey($this->spreadsheetId);
        $feed = $this->spreadsheetService->getWorksheetFeed($subquery);
        $id = $feed->entries[$index]->getId()->getText();
        return substr($id, strrpos($id , '/') + 1);
    }

}



