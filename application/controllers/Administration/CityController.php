<?php
/**
 * @author alex
 * @since 07.04.2011
 */
class Administration_CityController extends Default_Controller_AdministrationBase {

    /**
     * Table with all plz and cities
     * @author alex
     * @since 07.04.2011
     */
    public function indexAction(){

        // build select
        $db = Zend_Registry::get('dbAdapter');

        $select = $db
            ->select()
            ->from(array('c' => 'city'), array(
                'ID'            => 'c.id',
                __b('PLZ')           => 'c.plz',
                'city'  => 'c.city',
                __b('URL Restaurant') => 'c.restUrl',
                __b('URL Catering') => 'c.caterUrl',
                __b('URL Großhandel') => 'c.greatUrl',
                __b('Bundesland')   => 'c.state',
                __b('SEO Text')   => "IF(ISNULL(c.seoText) OR c.seoText='', 0, 1)",
            ))
            ->joinLeft(array('cp' => 'city'), 'cp.id = c.parentCityId', array(
                'PID'    => 'cp.id',
                __b('Übergeordnetes Liefergebiet') => 'cp.city',
            ))
            ->order('c.plz');

        // build grid
        $grid = Default_Helper::getTableGrid('city');
        $grid->setExport(array());
        $grid->setPagination(50);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('PID', array('hidden' => 1));
        $grid->updateColumn('city', array('title' => __b('Stadt/Bezirk')));
        $grid->updateColumn(__b('Übergeordnetes Liefergebiet'), array('decorator'=>'<a href="/administration_city/edit/cityId/{{PID}}">{{' . __b('Übergeordnetes Liefergebiet') . '}}</a>'));
        $grid->updateColumn(__b('SEO Text'), array('class' => 'seo-pupup-link', 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('SEO Text') . '}}'))));


        // add filters
        $filters = new Bvb_Grid_Filters();
        foreach (Yourdelivery_Model_City::getAllStates() as $s) {
            $states[$s['state']] = $s['state'];
        }
        $states[''] = __b('Alle');
        
        $filters->addFilter('ID')
            ->addFilter(__b('PLZ'))
            ->addFilter('city')
            ->addFilter(__b('SEO Text'), array('values' => array('' => __b('Alle'), '1' => __b('Ja'), '0' => __b('Nein'))))
            ->addFilter(__b('Bundesland'), array('values' => $states));
        $grid->addFilters($filters);

        // add row for editing plz
        $option = new Bvb_Grid_Extra_Column();
        $option
            ->position('right')
            ->name(__b('Optionen'))
            ->decorator(
                '<div>
                    <a href="/administration_city/edit/cityId/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/ alt="' . __b('Bearbeiten') . '"></a>
                </div>'
            );
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Create new city-plz entry
     * @author alex
     * @since 12.04.2011
     */
    public function createAction(){
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($request->getParam('cancel') !== null) {
                $this->_redirect('/administration_city');
            }
 
            $post = $request->getPost();
            
            $check = Yourdelivery_Model_DbTable_City::findByPlzAndCity($post['plz'], $post['city']);
            if (!is_null($check[0])) {
                $this->error('An entry with this city-plz already exists!');
                $this->_redirect('/administration_city/create');                    
            }

            $form = new Yourdelivery_Form_Administration_City_Create();
             
            $post['restUrl'] = Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $post['city'], $post['plz']));
            $post['caterUrl'] = Default_Helpers_Web::urlify(__('catering-%s-%s', $post['city'], $post['plz']));
            $post['greatUrl'] = Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $post['city'], $post['plz']));                

            // validate the form
            if ( $form->isValid($post) ) {
                $values = $form->getValues();
                
                $stateVals = explode("_", $values['state_stateId']);
                $values['state'] =  $stateVals[0];
                $values['stateId'] = $stateVals[1];

                if (!preg_match('/^[0-9\-]+$/', $values['plz'])) {
                    $this->error('plz can contain only digits and hyphen!');
                    $this->_redirect('/administration_city/create');                    
                }
                try {
                    $city = new Yourdelivery_Model_City();
                    $city->setData($values);
                    $city->save();
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error($e->getMessage());
                    $this->_redirect('/administration_city/create');
                }

                $this->logger->adminInfo(sprintf("City entry #%d created with plz => %s, city => %s, state => %s",
                        $city->getId(), $city->getPlz(), $city->getCity(), $city->getState()));

                $this->success(__b("City-plz entry created!"));
                $this->_redirect('/administration_city/edit/cityId/' . $city->getId());
            }
            else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * Edit city-plz entry
     * @author alex
     * @since 11.04.2011
     */
    public function editAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration_city');
        }
        
        $id = $request->getParam('cityId');

        if ($id === null) {
            $this->error(__b("No city range id defined!"));
            $this->_redirect('/administration_city/');
        }

        //create rating object
        try {
            $city = new Yourdelivery_Model_City($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This city-plz entry cannot be created!"));
            $this->_redirect('/administration_city/');
        }

        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_City_Edit();
            $post = $request->getPost();

            // validate the form
            if ( $form->isValid($post) ) {
                $values = $form->getValues();

                $stateVals = explode("_", $values['state_stateId']);
                $values['state'] =  $stateVals[0];
                $values['stateId'] = $stateVals[1];

                $check = Yourdelivery_Model_DbTable_City::findByPlzAndCity($city->getPlz(), $values['city']);

                $checkingEntry = $check[0];
                if ( !is_null($checkingEntry) && ($checkingEntry['id'] != $city->getId()) ) {
                    $this->error(__b("An entry with this city-plz-state combination already exists!"));
                    $this->_redirect('/administration_city/edit/cityId/' . $city->getId());
                }

                $oldValues = $city->getData();
                $city->setData($values);

                try {                    
                    if ($values['assembleurls'] == 1) {
                        if ($values['parentCityId'] != 0) {
                            $parentCity = new Yourdelivery_Model_City($values['parentCityId']);
                            $city->setRestUrl(Default_Helpers_Web::urlify(__('lieferservice-%s-%s-%s', $parentCity->getCity(), $values['city'], $city->getPlz())));                            
                            $city->setCaterUrl(Default_Helpers_Web::urlify(__('catering-%s-%s-%s', $parentCity->getCity(), $values['city'], $city->getPlz())));                            
                            $city->setGreatUrl(Default_Helpers_Web::urlify(__('grosshandel-%s-%s-%s', $parentCity->getCity(), $values['city'], $city->getPlz())));                            
                        }
                        else {
                            $city->setRestUrl(Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $values['city'], $city->getPlz())));
                            $city->setCaterUrl(Default_Helpers_Web::urlify(__('catering-%s-%s', $values['city'], $city->getPlz())));
                            $city->setGreatUrl(Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $values['city'], $city->getPlz())));                            
                        }                        
                    }
                    else {
                        if (strlen(trim($values['restUrl'])) == 0) {
                            $city->setRestUrl(Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $values['city'], $city->getPlz())));                            
                        }
                        else {
                            $city->setRestUrl(Default_Helpers_Web::urlify($values['restUrl']));
                        }
                        
                        if (strlen(trim($values['caterUrl'])) == 0) {
                            $city->setCaterUrl(Default_Helpers_Web::urlify(__('catering-%s-%s', $values['city'], $city->getPlz())));                            
                        }
                        else {
                            $city->setCaterUrl(Default_Helpers_Web::urlify($values['caterUrl']));
                        }
                        
                        if (strlen(trim($values['greatUrl'])) == 0) {
                            $city->setGreatUrl(Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $values['city'], $city->getPlz())));                            
                        }
                        else {
                            $city->setGreatUrl(Default_Helpers_Web::urlify($values['greatUrl']));
                        }
                    }
                    
                    $city->uncache();
                    $city->save();
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error($e->getMessage());
                    $this->_redirect('/administration_city/edit/cityId/' . $city->getId());
                }

                $this->logger->adminInfo(sprintf("Changed data for city entry %d with plz %s from (city => %s, state => %s, parent => %s) to (city => %s, state => %s, parent => %s)",
                        $city->getId(), $city->getPlz(), $oldValues['city'], $oldValues['state'], $oldValues['parentCityId'], $values['city'], $values['state'], $values['parentCityId']));
                
                $this->success(__b("City entry was edited"));
                $this->_redirect('/administration_city/');
            }
            else {
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('city', $city);
    }

}