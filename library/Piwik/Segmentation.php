<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Use Piwik to segment the entire available data using this api
 *
 * @author mlaug
 */
class Piwik_Segmentation {

    protected $url = null;
    protected $xsl = null;
    protected $params = array();
    protected $segment = array();
    protected $logger = null;

    public function __construct() {
        $this->logger = Zend_Registry::get('logger');

        //build up basic url
        $config = Zend_Registry::get('configuration');
        $this->url = $config->piwik->url . '?token_auth=' . $config->piwik->token;
        $this->url .= '&idSite=' . $config->piwik->id . '&module=API';

        //set default parameters
        $this->setParam('format', 'xml');
        $this->setParam('period', 'range');
        $this->setParam('date', sprintf('2009-01-01,2020-01-01'));
    }

    /**
     * set used method for segmentation
     * @author mlaug
     * @since 09.06.2011
     * @param string $cls
     * @param string $func 
     */
    public function setMethod($cls, $func) {
        $call = ucfirst($cls) . '.' . $func;
        $this->setStyleSheet(strtolower($cls));
        switch (ucfirst($cls)) {
            default :
                throw new Piwik_Segmentation_InvalidMethod($call . ' is not a valid piwik call');

            case 'Referers':
                break;
            case 'Goals':
                break;
            case 'Actions':
                break;
        }
        
        $this->setParam('method',$call);
        
    }

    /**
     * @author mlaug
     * @since 09.06.2011
     * @param string $name
     * @param string $value 
     */
    public function setParam($name, $value) {
        $this->params[$name] = $value;
    }
    
    /**
     *
     * @param string $segment
     * @param string $value
     * @param string $compare 
     */
    public function addSegment($segment, $value, $compare = '==', $join = ';'){
        $this->segment[] = array(
            'segment' => $segment, 
            'value' => $value,
            'compare' => $compare,
            'join' => $join
        );
    }

    /**
     * @author mlaug
     * @since 09.06.2011
     * @return string
     */
    public function request() {
        $url = $this->url;
        foreach($this->params as $name => $value){
            $url .= sprintf('&%s=%s',$name,$value);
        }
        if ( count($this->segment) > 0 ){
            $url .= '&segment=';
            $segments = array();
            $join = "";
            foreach($this->segment as $segment ){
                $url .= $join . $segment['segment'] . $segment['compare'] . $segment['value'];
                $join = $segment['join'];
            }
        }
        $this->logger->debug('PIWIK: calling for ' . $url);
        return file_get_contents($url);
    }
    
    /**
     * @author mlaug
     * @since 09.06.2011
     * @return string 
     */
    public function getHtml(){
        return $this->parseXml($this->request());
    }

    /**
     * parse xml output from piwik to valid html via xsl
     * @author mlaug
     * @since 09.06.2011
     * @param string $xmlData
     * @return string
     */
    private function parseXml($xmlData) {
        
        if ($this->params['format'] != 'xml') {
            $this->logger->warn('PIWIK: could not parse output, because of invalid format ' . $this->params['format']);
            throw new Piwik_Segmentation_NeedXmlOutput('format must be xml, but is ' . $this->params['format']);
        }
        
        //translate id goals
        $table = new Yourdelivery_Model_DbTable_Piwik_Goals();
        $goals = $table->fetchAll();        
        foreach($goals as $goal){
            $xmlData = str_replace(sprintf("idgoal='%d'",$goal['goalId']),sprintf("idgoal='%s'",$goal['goalName']),$xmlData);
        }
        $xmlData = preg_replace("/idgoal='\d'/","idgoal='shit, no clue'",$xmlData);
        
        $xml = new DOMDocument();
        $xml->loadXML($xmlData);
        $xslt = new XSLTProcessor();
        $xsl = new DOMDocument();
        $xsl->load($this->xsl, LIBXML_NOCDATA);
        $xslt->importStylesheet($xsl);
        return $xslt->transformToXML($xml);
    }   
    

    /**
     * set stylesheet according to called method or define xsl sheet manually
     * @author mlaug
     * @since 09.06.2011
     * @param string $xsl
     */
    private function setStyleSheet($xsl) {
        $file = APPLICATION_PATH . '/templates/xsl/piwik/' . $xsl . '.xsl';
        if (!file_exists($file)) {
            throw new Piwik_Segmentation_XslNotFound('could not find xsl sheet ' . $file);
        }
        $this->xsl = $file;
    }

}

class Piwik_Segmentation_InvalidMethod extends Zend_Exception {
    
}

class Piwik_Segmentation_XslNotFound extends Zend_Exception {
    
}

class Piwik_Segmentation_NeedXmlOutput extends Zend_Exception {
    
}

?>
