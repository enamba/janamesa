<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BulkeditController
 *
 * @author daniel
 */
class Administration_Service_BulkeditController extends Default_Controller_AdministrationBase {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.03.2012
     */
    public function offlineAction() {

        $db = Zend_Registry::get('dbAdapter');

        if ($this->getRequest()->isPost()) {
            $offlineIds = $this->getRequest()->getParam('off');
            
            $count = 0;
            $admin = $this->session->admin;
            foreach ($offlineIds as $id => $value) {

                $service = new Yourdelivery_Model_Servicetype_Restaurant($id);

                $service->setIsOnline(0);
                $service->setStatus(12);
                $service->uncache();
                $service->save();
                
                $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                $comment->setMasterAdmin(1);
                $comment->setAdminId($admin->getId());
                $comment->setRestaurantId($service->getId());
                $comment->setComment(__b("Bulkedit Offline gesetzt"));
                $comment->setTime(date("Y-m-d H:i:s", time()));
                $comment->save();
                
                $count++;
            }
            
            if($this->getRequest()->getParam('notify') && $count > 0) {
                // Using config-based locale during composing and sending e-mail
                $this->_restoreLocale();
                Yourdelivery_Sender_Email::osTicket(
                                "Sebastian Ohrmann",
                                __b("Dienstleister offline gestellt"),
                                __b("Folgende Dienstleister wurden per bulk edit offline (status urlaub) gestellt (ids): %s", implode(",", array_keys($offlineIds))));
                $this->_overrideLocale();
            }
            
            
            $this->info("Es wurden ".$count." Restaurants Offline  gestellt (Status Urlaub)");
        }



        $select = $db
                ->select()
                ->from(array('r' => 'restaurants'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'r.created',
                    __b('Name') => 'r.name',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (r.street, " ", r.hausnr)'),
                    __b('PLZ') => 'r.plz',
                    __b('Kundennummer') => 'r.customerNr',
                    __b('Zuständige') => 'r.id',
                    __b('Franchise') => 'r.franchiseTypeId',
                    __b('Stadt') => 'COALESCE(CONCAT(pct.city," (",ct.city,")"), ct.city)',
                ))
                ->joinLeft(array('ct' => 'city'), 'r.cityId = ct.id', array())
                ->joinLeft(array('pct' => 'city'), 'ct.parentCityId = pct.id', array())
                ->where('r.deleted = 0  AND r.isOnline >= 1')
                ->group('r.id')
                ->order('r.id DESC');

        $franchises = Yourdelivery_Model_Servicetype_Franchise::all();
        $franchiseType = array('' => __b('Alle'));
        foreach ($franchises as $franchise) {
            $franchiseType[$franchise['id']] = $franchise['name'];
        }
        $filters = new Bvb_Grid_Filters();

        $filters->addFilter(__b('Franchise'), array('values' => $franchiseType))
                ->addFilter(__b('ID'))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Adresse'))
                ->addFilter(__b('PLZ'))
                ->addFilter(__b('Kundennummer'))
                ->addFilter(__b('Stadt'));

        //  echo $select; die();
        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->addFilters($filters);
        $grid->setExport(array());
        $grid->setRecordsPerPage(500);
        $grid->updateColumn(__b('Franchise'), array('title' => 'Franchise', 'callback' => array('function' => 'getFranchise', 'params' => array('{{'.__b('Franchise').'}}'))));
        $grid->updateColumn(__b('Zuständige'), array('callback' => array('function' => 'getAdmins', 'params' => array('{{'.__b('ID').'}}'))));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{'.__b('ID').'}}'));
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('left')
                ->name('Auswahl')
                ->decorator(
                        '<input class="yd-check-offline" type="checkbox" name="off[{{'.__b('ID').'}}]" />'
        );
        $grid->addExtraColumns($option);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.03.2012
     */
    public function faxserviceAction() {

        $storage = new Default_File_Storage();
        $storage->setSubFolder("backup");
        $storage->setSubFolder("fax");
        $db = Zend_Registry::get('dbAdapter');
        $faxServices = array('interfax', 'retarus');

        if ($this->getRequest()->isPost()) {

            $restore = $this->getRequest()->getParam('restore');

            if ($restore) {
                foreach ($faxServices as $faxService) {
                    $ids = file_get_contents($storage->getCurrentFolder() . "/" . $faxService . ".txt");
                    $db->update('restaurants', array('faxService' => $faxService), array("id IN (" . $ids . ")"));
                    $storage->delete($faxService . ".txt");
                }
            } else {
                if ($storage->exists('retarus.txt') || $storage->exists('interfax.txt')) {
                    $this->error('Faxdienstleister wurde schon umgestellt.');
                    $this->_setViewRestoreMode();
                    return;
                }

                $faxServiceParam = $this->getRequest()->getParam('faxservice');
                if ($faxServiceParam && in_array($faxServiceParam, $faxServices)) {
                    foreach ($faxServices as $faxService) {
                        $select = $db->select()
                                ->from('restaurants', array('id'))
                                ->where("faxService LIKE ?", $faxService);
                        $results = $db->fetchAll($select);
                        $tmp = array();
                        foreach ($results as $result) {
                            $tmp[] = $result['id'];
                        }
                        $storage->store($faxService . ".txt", implode(",", $tmp));
                    }
                    $db->update('restaurants', array('faxService' => $faxServiceParam), "");
                }
            }
        }


        if ($storage->exists('retarus.txt') || $storage->exists('interfax.txt')) {
            $this->_setViewRestoreMode();
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 01.03.2012
     */
    protected function _setViewRestoreMode() {
        $db = Zend_Registry::get('dbAdapter');
        $this->view->restore = true;
        $select = $db->select()->from('restaurants', array('faxService'))->group('faxService');

        $result = $db->fetchAll($select);

        if (count($result) > 1) {
            $this->error('Ein Fehler ist aufgetreten. Bitte kontaktieren Sie die IT.');
        } else {
            $this->view->currentService = $result[0]['faxService'];
        }
    }

}

?>
