<?php

/**
 * Autocomplete controller
 *
 * @author vpriem
 */
class AutocompleteController extends Default_Controller_RequestBase {

    /**
     * @var string
     */
    private $_term = null;

    /**
     * Init
     * @author vpriem
     */
    public function init() {

        // print only json
        $this->_helper->viewRenderer->setNoRender(true);

        // set term
        $this->_term = $this->_request->getParam('term');
    }

    /**
     * PLZ autocompletion
     * Save the query result into memcache
     * Find the user request using PHP
     * Cache every result using JS
     * @author vpriem
     * @since 18.11.2011
     * @modified jnaie 16.06.2012
     */
    public function plzAction() {

        $this->setCache(28800); //8 hours
        $serviceId = $this->_getParam('service', 0);
        $cacheId = 'plzAutocomplete' . ($serviceId > 0 ? $serviceId : '');
        
        $plzBeginning = null;
        // too many PLZs in brasil to fit in an array. only get the PLZs where the first two digits match
        if (!$serviceId && $this->config->domain->base == 'janamesa.com.br') {
            $plzBeginning = substr($this->_term, 0, 2);
            $cacheId .= $plzBeginning;
        }

        $plz = Default_Helpers_Cache::load($cacheId);
        if (!is_array($plz)) {
            $plz = Yourdelivery_Model_Autocomplete::plz($serviceId, $plzBeginning);
            Default_Helpers_Cache::store($cacheId, $plz);
        }
        if (!is_array($plz)) {
            return $this->_json(array());
        }

        $json = array();
        foreach ($plz as $p) {
            if (strpos($p['value'], $this->_term) === 0) {
                $json[] = $p;
            }
        }
        if (isset($this->config->ajax)
            && isset($this->config->ajax->maxautocomplete)) {
            $json =
                array_slice($json, 0, $this->config->ajax->maxautocomplete);
        }

        $this->_json($json);
    }

    /**
     * Street autocompletion
     * @author Matthias Laug <laug@lieferando.de>
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 18.04.2012
     */
    public function streetAction() {

        $this->setCache(28800); //8 hours
        $request = $this->getRequest();
        $city = $request->getParam('city');
        $serviceId = (integer) $request->getParam('service', 0);

        //could not find city
        if (count(Yourdelivery_Model_City::getByCity($city)) == 0) {
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $cacheId = md5("streetAutocomplete" . $city . "s" . $serviceId);
        $street = Default_Helpers_Cache::load($cacheId);
        if (!is_array($street)) {
            $street =
                Yourdelivery_Model_Autocomplete::street($city, $serviceId);
            Default_Helpers_Cache::store($cacheId, $street);
        }

        if (!is_array($street) || count($street) == 0) {
            return $this->_json(array());
        }

        // For Pyszne.pl we exclude from term prefix Polish words meaning just 'str.'
        if (substr($this->config->domain->base, -3) == '.pl') {
            $this->_term = preg_replace('/^[\s]*ul[\s.]/', '', $this->_term);
        }
        $this->findExtendedTerm($street);
    }

    /**
     * city autocompletion
     * @author Matthias Laug <laug@lieferando.de>
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 18.04.2012
     */
    public function cityAction() {

        $this->setCache(28800); //8 hours
        $serviceId = (integer) $this->getRequest()->getParam('service', 0);

        $cacheId = md5("cityAutocomplete" . $serviceId);
        $city = Default_Helpers_Cache::load($cacheId);
        if (!is_array($city)) {
            $city = Yourdelivery_Model_Autocomplete::city($serviceId);
            Default_Helpers_Cache::store($cacheId, $city);
        }
        if (!is_array($city)) {
            return $this->_json(array());
        }

        $this->findExtendedTerm($city);
    }

    /**
     * Converts passed text to ascii, converting diactritic characters, additionally transforms to lowercase when needed
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 15.05.2012
     *
     * @param string $text
     * @param boolean $toLowerCase
     * @return string
     */
    protected function toAscii($text, $toLowerCase = true) {
        $ascii = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $text);
        return ($toLowerCase) ? strtolower($ascii) : $ascii;
    }

    /**
     * Uses unified mechanism to find potentially multipart term within passed set retrieved from DB,
     * then sets up JSON response containing search results
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 15.05.2012
     *
     * @param array $fullSet
     * @return void
     */
    protected function findExtendedTerm($fullSet) {
        $json = array();
        $jsonCount = 0;
        $limit = $this->getRequest()->getParam('limit', 10);

        // Converting term to ascii-encoded chunks (separated by space, dot or hyphen)
        $termChunks = array();
        foreach (preg_split('/[\s.-]+/', $this->toAscii($this->_term), 0, PREG_SPLIT_NO_EMPTY) as $term) {
            $termChunks[] = $term;
        }
        if (empty($termChunks)) {
            // nothing to be found
            return $this->getResponse()->setHttpResponseCode(404);
        }

        // any term chunk must be found inside full name chunks
        foreach ($fullSet as $item) {
            $nameChunks =
                preg_split('/[\s.-]+/', $this->toAscii($item['value']), 0, PREG_SPLIT_NO_EMPTY);
            foreach ($termChunks as $termChunk) {
                foreach ($nameChunks as $nameChunk) {
                    if (strpos($nameChunk, $termChunk) === 0) {
                        // current term chunk found - going to next term chunk checking iteration
                        continue 2;
                    }
                }
                // term chunk not found - going to next item interaction
                continue 2;
            }
            $json[] = $item;
            $jsonCount++;

            if ($limit <= $jsonCount) {
                // enough
                break;
            }
        }

        if (!$jsonCount) {
            return $this->getResponse()->setHttpResponseCode(404);
        }

        $this->_json($json);
    }

    /**
     * CRM autocompletion
     * @author alex
     * @since 21.06.2011
     */
    public function crmAction() {

        $request = $this->getRequest();
        $type = $request->getParam('type', 'service');

        $json =
            json_encode(Yourdelivery_Model_Autocomplete::crm($type));

        echo $json;
    }

    /**
     * Employees email autocompletion, for companies, based on a certian group
     * @since 14.08.2010
     * @author mlaug
     */
    public function budgetgroupAction() {

        $customer = $this->getCustomer();

        if (!$customer->isEmployee()) {
            return;
        }

        if ($this->_term === null || strlen($this->_term) < 1) {
            return;
        }

        echo Zend_Json::encode(Yourdelivery_Model_Autocomplete::budgetgroup($customer->getBudget()->getId(), $this->_term));
    }

    /**
     * Employees email autocompletion, for companies
     * @author vpriem
     */
    public function employeesAction() {

        $customer = $this->getCustomer();

        if (!$customer->isEmployee()) {
            return;
        }

        if ($this->_term === null || strlen($this->_term) < 1) {
            return;
        }

        echo Zend_Json::encode(Yourdelivery_Model_Autocomplete::employees($customer->getCompany()->getId(), $this->_term));
    }

    /**
     * Projectnumbers autocompletion, for companies
     * @author vpriem
     */
    public function projectnumbersAction() {

        $customer = $this->getCustomer();

        if (!$customer->isEmployee()) {
            return;
        }

        if ($this->_term === null || strlen($this->_term) < 1) {
            return;
        }

        echo Zend_Json::encode(Yourdelivery_Model_Autocomplete::projectnumbers($customer->getCompany()->getId(), $this->_term));
    }

    /**
     * Meal names autocomplete
     * @author alex
     * @since 07.10.2010
     */
    public function mealsAction() {

        if ($this->_term === null || strlen($this->_term) < 1) {
            return;
        }

        echo Zend_Json::encode(Yourdelivery_Model_Autocomplete::meals($this->_term));
    }

    /**
     * Meal descriptions autocomplete
     * @author alex
     * @since 07.10.2010
     */
    public function mealdescriptionsAction() {

        if ($this->_term === null || strlen($this->_term) < 1) {
            return;
        }

        echo Zend_Json::encode(Yourdelivery_Model_Autocomplete::mealdescriptions($this->_term));
    }

    public function cityplzAction() {   
        $this->setCache(28800); //8 hours
        $cacheId = "plzAutocomplete";
        $plz = Default_Helpers_Cache::load($cacheId);
        if (!is_array($plz)) {
            $plz = Yourdelivery_Model_Autocomplete::plz();
            Default_Helpers_Cache::store($cacheId, $plz);
        }
        if (!is_array($plz)) {
            return $this->_json(array());
        }

        $this->_json($plz);
    }

}
