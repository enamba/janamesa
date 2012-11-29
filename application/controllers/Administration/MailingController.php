<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MailingController
 *
 * @author daniel
 */
class Administration_MailingController extends Default_Controller_AdministrationBase {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     */
    public function indexAction() {
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('r' => 'mailing_optivo'), array(
                    __b('ID') => 'id',
                    __b('Name') => 'name',
                    __b('Status') => 'status',
                    __b('Erstellt') => 'created',
                    __b('Start') => 'start',
                    __b('Ende') => 'end',
                    __b('Optionen') => 'id'
                ))
                ->order('r.id DESC');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'Default_Helpers_Grid_Mailing::mailingStatus', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Start'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Start') . '}})'))));
        $grid->updateColumn(__b('Ende'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Ende') . '}})'))));
        $grid->updateColumn(__b('Erstellt'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Erstellt') . '}})'))));
        $grid->updateColumn(__b('Optionen'), array('callback' => array('function' => 'Default_Helpers_Grid_Mailing::mailingOptions', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('Optionen') . '}}'))));

        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     */
    public function createAction() {
        $this->view->form = $form = new Yourdelivery_Form_Administration_Mailing_Optivo();
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_mailing');
        }
        
        $this->_save($request, $form, new Yourdelivery_Model_Mailing_Optivo());                             
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     */
    public function editAction() {

        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_mailing');
        }

        if (is_null($request->getParam('id'))) {
            $this->error(__b("Diese Mailingaktion gibt es nicht!"));
            $this->_redirect('/administration_mailing');
        }
        
        try {
            $mailing = new Yourdelivery_Model_Mailing_Optivo($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Mailingaktion gibt es nicht!"));
            $this->_redirect('/administration_mailing');
        }

        $row = $mailing->getTable()->getCurrent()->toArray();
        $this->view->form = $form = new Yourdelivery_Form_Administration_Mailing_Optivo();
        $form->setDefaults($row);
        $parameters = explode(';', $row['parameters']);
        $form->setDefault('parameters', $parameters);
        $this->view->mailing = $mailing;

        $this->_save($request, $form, $mailing);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.07.2012 
     */
    protected function _save($request, $form, $mailing) {
        if ($request->isPost()) {
            $post = $request->getPost();
            if ($form->isValid($post)) {
                
                $values = $form->getValues();

                $cityIds = $values['cityIds'];

                $values['parameters'] = implode(';', $values['parameters']);
                unset($values['cityIds']);

                $mailing->setData($values);
                $mailing->save();

                if ($cityIds) {
                    $mailing->setCitys($cityIds);
                }
                
               return $this->_redirect('/administration_mailing'); 
            }
        }
    }

}

