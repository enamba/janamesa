<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * billing assets management
 *
 * @author alex
 */
class Administration_BillingassetController extends Default_Controller_AdministrationBase{

    public function  preDispatch() {
        parent::preDispatch();
        $this->view->assign('navbilling', 'active');
    }

    /**
     * create new billing asset
     * @author alex
     */
    public function createAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration/billingassets');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            //create new billing asset
            $form = new Yourdelivery_Form_Administration_Billingasset_Edit();
            if($form->isValid($post)) {
                $asset = new Yourdelivery_Model_BillingAsset();

                // not using $form->getValues(), because departmentId will be 0 then, but must be NULL
                $values = $post;

                if( ($values['timeFrom'] == '00.00.0000') || (strlen(trim($values['timeFrom'])) == 0) ){
                    //$values['timeFrom'] = '0000-00-00';
                    $this->error(__b("Zeitangabe fehlt"));
                    $this->_redirect('/administration_billingasset/create');
                }
                else {
                    $tf = explode(".", $values['timeFrom']);
                    $values['timeFrom'] = $tf[2] . '-' . $tf[1] . '-' . $tf[0];
                }

                if( ($values['timeUntil'] != '00.00.0000') && (strlen(trim($values['timeUntil'])) != 0) ){
                    $tu = explode(".", $values['timeUntil']);
                    $values['timeUntil'] = $tu[2] . '-' . $tu[1] . '-' . $tu[0];
                }
                
                //convert entered euro amount to cents and convert from decimal comma separation to period separation
                $values['total'] = str_replace(",", ".", $values['total']);
                $values['total'] *= 100;

                //convert entered euro amount to cents and convert from decimal comma separation to period separation
                $values['couriertotal'] = str_replace(",", ".", $values['couriertotal']);
                $values['couriertotal'] *= 100;

                if ($values['brutto-checkbox'] == 1) {
                    // if total is given as brutto, calculate the value without mwst and save it
                    $values['total'] = $values['total']*100/(100 + $values['mwst']);
                }

                $asset->setData($values);
                $asset->save();
                $this->_redirect('/administration/billingassets');
            }
            else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        }

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $courierTable = new Yourdelivery_Model_DbTable_Courier();
        $this->view->assign('courierIds', $courierTable->getDistinctNameId());
    }

    /**
    * edit billing asset
    * @author alex
    */
    public function editAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/billingassets');
        }

        if(is_null($request->getParam('id'))) {
            $this->error(__b("This billing asset is non-existant"));
            $this->_redirect('/administration/billingassets');
        }

        //create billing asset object
        try {
            $asset = new Yourdelivery_Model_BillingAsset($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This billing asset is non-existant"));
            $this->_redirect('/administration/billingassets');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            // if the bill was already sent, we have no drop-down menu for company, must be set hier
            if (intval($asset->getBillCompany()) != 0 ) {
                $post['companyId'] = $asset->getCompanyId();
            }

            $form = new Yourdelivery_Form_Administration_Billingasset_Edit();
            if ( $form->isValid($post) ){
                // not using $form->getValues(), because departmentId will be 0 then, but must be NULL if not set
                $values = $post;

                // If bill was not set, we had drop-menus, so set zero values to null
                if (intval($asset->getBillCompany()) == 0 ) {
                    if ($post['projectnumberId'] == 0) {
                        $values['projectnumberId'] = null;
                    }

                    if ($post['departmentId'] == 0) {
                        $values['departmentId'] = null;
                    }
                }

                if( ($values['timeFrom'] == '00.00.0000') || (strlen(trim($values['timeFrom'])) == 0) ){
                    //$values['timeFrom'] = '0000-00-00';
                    $this->error(__b("Time missing"));
                    $this->_redirect('/administration_billingasset/edit/id/'. $request->getParam('id'));
                }
                else {
                    $tf = explode(".", $values['timeFrom']);
                    $values['timeFrom'] = $tf[2] . '-' . $tf[1] . '-' . $tf[0];
                }

                if( ($values['timeUntil'] != '00.00.0000') && (strlen(trim($values['timeUntil'])) != 0) ){
                    $tu = explode(".", $values['timeUntil']);
                    $values['timeUntil'] = $tu[2] . '-' . $tu[1] . '-' . $tu[0];
                }

                //convert entered euro amount to cents and convert from decimal comma separation to period separation
                $values['total'] = priceToInt2($values['total']);
                $values['couriertotal'] = priceToInt2($values['couriertotal']);

                //save new data
                $asset->setData($values);
                $asset->save();
                $this->_redirect('/administration/billingassets');
            }
            else{
                $this->error($form->getMessages());
                $this->_redirect('/administration_billingasset/edit/id/'. $request->getParam('id'));
            }
        }

        $this->view->assign('asset', $asset);

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $courierTable = new Yourdelivery_Model_DbTable_Courier();
        $this->view->assign('courierIds', $courierTable->getDistinctNameId());

        $company = new Yourdelivery_Model_Company($asset->getCompanyId());
        $this->view->assign('projectnumbers', $company->getProjectNumbers());
        $this->view->assign('departments', $company->getDepartments());
    }

    /**
     * delete billing asset
     * @author alex
     */
     public function deleteAction() {
        $id = $this->getRequest()->getParam('id');

        if(is_null($id)) {
            $this->error(__b("This billing asset is non-existant"));
            $this->_redirect('/administration/billingassets');
        }

        Yourdelivery_Model_DbTable_BillingAsset::remove($id);
        $this->_redirect('/administration/billingassets');
    }

    /**
     * Tells whether locale overriding is forbidden within whole controller
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        return true;
    }
}
