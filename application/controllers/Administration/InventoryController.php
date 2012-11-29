<?php

/**
 * Get a current state of an item
 * @author mlaug
 * @since 20.04.2011
 * @param integer $id
 * @param string $type
 * @return string
 */
function getGridState($id, $type) {
    $table = new Yourdelivery_Model_DbTable_Inventory_Status();
    
    $state = $table->getCurrentState($id, $type);
    if (is_array($state)) {
        return '<b>' . $state['status'] . '</b>' . ($state['comment'] ? '<br />' . __b("Kommentar: ") . $state['comment'] : '');
    }
    
    return '';
}

/**
 * @author mlaug
 */
class Administration_InventoryController extends Default_Controller_AdministrationBase {

    public function init() {
        
        parent::init();
        
        $this->view->colors = array(
            '',
            'weiss',
            'schwarz',
            'grau',
            'hellgelb',
            'gelb',
            'dunkelgelb',
            'hellorange',
            'orange',
            'dunkelorange',
            'hellgrün',
            'grün',
            'dunkelgrün',
            'hellblau',
            'blau',
            'dunkelblau',
            'helllila',
            'lila',
            'dunkellila',
            'hellrot',
            'rot',
            'dunkelrot'
        );
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function indexAction() {
        $this->_forward('overview');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     * @param Zend_Form $form
     * @return type 
     */
    private function _subChecks(Zend_Form $form) {
        //some additional checks
        if ($form->getValue('countFlyer') > 0) {
            $type = $form->getValue('typeFlyer');
            $color1 = $form->getValue('colorOneFlyer');
            $color2 = $form->getValue('colorTwoFlyer');
            $color3 = $form->getValue('colorThreeFlyer');

            if (empty($type) || empty($color1) || empty($color2) || empty($color3)) {
                $this->error(__b("Bitte wählen sie Typ und Farbe des Fylers aus"));
                return false;
            }
        }

        return true;
    }

    /**
     * store or edit a new item
     * @author mlaug
     * @since 20.04.2011
     * @param Yourdelivery_Model_Inventory $item
     * @return void
     */
    private function _store(Yourdelivery_Model_Inventory $item) {
        $request = $this->getRequest();
        $form = new Yourdelivery_Form_Administration_Inventory_Create();
        if ($form->isValid($request->getPost())) {

            if (!$this->_subChecks($form)) {
                return;
            }

            $values = $form->getSubForm('item')->getValues();
            $values['item']['printerNextDate'] = date('Y-m-d H:i:s', strtotime($values['item']['printerNextDate']));
            $item->setData($values['item']);
            $item->save();

            //store all the stati for creation         
            $item->addComment('needs', $form->getSubform('status')->getValue('statusCommentNeeds'), $form->getSubform('status')->getValue('statusNeeds'), $this->session->admin->getId());
            $item->addComment('printer', $form->getSubform('status')->getValue('statusCommentPrinterCost'), $form->getSubform('status')->getValue('statusPrinterCost'), $this->session->admin->getId());
            $item->addComment('website', $form->getSubform('status')->getValue('statusCommentWebsite'), $form->getSubform('status')->getValue('statusWebsite'), $this->session->admin->getId());
            $item->addComment('flyer', $form->getSubform('status')->getValue('statusCommentFlyer'), $form->getSubform('status')->getValue('statusFlyer'), $this->session->admin->getId());
            $item->addComment('terminal', $form->getSubform('status')->getValue('statusCommentTerminal'), $form->getSubform('status')->getValue('statusTerminal'), $this->session->admin->getId());

            $this->_redirect('administration_inventory/overview');
        }
        else {
            $this->view->post = $request->getPost();
            $this->error($form->getMessages());
            $this->error(__b("Konnte Posten nicht anlegen"));
        }
    }

    /**
     * deploy a grid on given select statement
     * @author mlaug
     * @since 20.04.2011
     * @param Zend_Db_Select $select
     * @param type $type
     * @return string 
     */
    private function _deployGrid($select, $type) {

        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn('SID', array('hidden' => 1));
        $grid->updateColumn('RestaurantId',  array('searchType' => '='));
        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Status'))
                ->callback(array('function' => 'getGridState', 'params' => array('{{SID}}', $type)));

        $edit = new Bvb_Grid_Extra_Column();
        $edit
                ->position('right')
                ->name(__b('Optionen'))
                ->decorator('<a href="/administration_inventory/edit/id/{{SID}}">' . __b("Bearbeiten") . '</a>');

        $grid->addExtraColumns($option, $edit);
        
        return $grid->deploy();
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function overviewAction() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('s' => 'inventory'), array(
                    __b('SID') => 's.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Pizza Karton 26x26x4') => 'countCanton2626',
                    __b('Pizza Karton 28x28x4') => 'countCanton2828',
                    __b('Pizza Karton 32x32x4') => 'countCanton3232',
                    __b('Servietten 2lagig, 33x33') => 'countServicing',
                    __b('Plastiktüten') => 'countBags',
                    __b('Chopsticks') => 'countSticks'
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(__b('RestaurantId') => 'r.id', __b('Restaurant') => 'r.name'))
                ->order("s.id DESC");

        // build grid
        $this->view->grid = $this->_deployGrid($select, 'needs');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function overviewflyerAction() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('s' => 'inventory'), array(
                    __b('SID') => 's.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Druckvolumen Lieferando') => 'countFlyer',
                    __b('Farbe 1') => 'colorOneFlyer',
                    __b('Farbe 2') => 'colorTwoFlyer',
                    __b('Farbe 3') => 'colorThreeFlyer',
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(__b('RestaurantId') => 'r.id', __b('Restaurant') => 'r.name'))
                ->order("s.id DESC");

        // build grid
        $this->view->grid = $this->_deployGrid($select, 'flyer');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function overviewprinterAction() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('s' => 'inventory'), array(
                    __b('SID') => 's.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Kostenbeteiligung') => 'printerCostPercent',
                    __b('Eigenes Druckvolumen') => 'printerOwn',
                    __b('Format') => 'printerFormat',
                    __b('Priorität') => 'printerPrio',
                    __b('nächster Drucktermin') => 'printerNextDate'
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(__b('RestaurantId') => 'r.id', __b('Restaurant') => 'r.name'))
                ->where('printerCostPercent > 0')
                ->where('printerOwn > 0')
                ->where('printerPrio > 0')
                ->where('printerFormat > 0')
                ->order("s.id DESC");

        // build grid
        $this->view->grid = $this->_deployGrid($select, 'printer');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function overviewwebsiteAction() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('s' => 'inventory'), array(
                    __b('SID') => 's.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Website gewünscht') => 'website',
                    __b('Kosten') => 'websiteCost',
                    __b('Farbe 1') => 'colorOneWebsite',
                    __b('Farbe 2') => 'colorTwoWebsite',
                    __b('Farbe 3') => 'colorThreeWebsite'
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(__b('RestaurantId') => 'r.id', __b('Restaurant') => 'r.name'))
                ->order("s.id DESC");

        // build grid
        $this->view->grid = $this->_deployGrid($select, 'website');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function overviewterminalAction() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('s' => 'inventory'), array(
                    __b('SID') => 's.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Terminal gewünscht') => 'terminal',
                    __b('Kostenbeteiligung') => 'terminalBail'
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = s.restaurantId', array(__b('RestaurantId') => 'r.id', __b('Restaurant') => 'r.name'))
                ->order("s.id DESC");

        // build grid
        $this->view->grid = $this->_deployGrid($select, 'terminal');
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function createAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $item = new Yourdelivery_Model_Inventory();
            $this->_store($item);
        }   
        
        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());
    }

    /**
     * @author mlaug
     * @since 20.04.2011
     */
    public function editAction() {
        $request = $this->getRequest();
        
        $id = (integer) $request->getParam('id');
        if ($id <= 0) {
            $this->error(__b("Konnte Element nicht finden"));
            $this->_redirect('/administration_inventory/overview');
        }

        try {
            $item = new Yourdelivery_Model_Inventory($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Konnte Element nicht finden"));
            $this->_redirect('/administration_inventory/overview');
        }

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $this->view->item = $item;

        if ($request->isPost()) {
            $this->_store($item);
        }
    }

}
