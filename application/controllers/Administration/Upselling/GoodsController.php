<?php

/**
 * Get grid download link for billing
 * @author Vincent Priem <priem@lieferando.de>
 * @since 16.06.2011
 * @param string $restaurant
 * @param integer $billingId
 * @param integer $billingNr
 * @return string
 */
function getGridRestaurant($restaurant, $billingId, $billingNr) {
    
    $html = $restaurant;

    // pdf
    $file = APPLICATION_PATH . '/../storage/billing/upselling/' . substr($billingNr, 2, 4) . "/" . $billingNr . ".pdf";
    if (file_exists($file)) {
        $html .= '<br /><a href="/download/bill/' . Default_Helpers_Crypt::hash($billingId) . '/pdf">'.__b("PDF Rechnung herunterladen").'</a>';
    }
    
    return $html;
}

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 27.06.2011
 */
class Administration_Upselling_GoodsController extends Default_Controller_AdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 30.12.2011
     * @return Yourdelivery_Model_Upselling_Goods 
     */
    private function _getItem($id) {
        
        // create model
        if ($id !== null) {
            try {
                $item = new Yourdelivery_Model_Upselling_Goods($id);
                if (!$item->getId()) {
                    unset($item);
                }
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return $item;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2011
     */
    public function indexAction() {

        // get request
        $request = $this->getRequest();
        
        // get/set status
        $status = $request->getParam('s');
        if ($status !== null) {
            if ($status == "off") {
                $status = null;
            }
            $this->session->upsellingGoodsStatus = $status;
        }
        $status = $this->session->upsellingGoodsStatus;
        
        // create select
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db
                ->select()
                ->from(array('ug' => 'upselling_goods'), array(
                    __b('GID') => 'ug.id',
                    __b('RestaurantId') => 'r.id',
                    __b('Restaurant') => 'r.name',
                    __b('Pizza Karton 26x26x4') => 'ug.countCanton2626',
                    'ug.unitCanton2626',
                    __b('Pizza Karton 26x26x4 N') => 'ug.countCanton2626N',
                    'ug.unitCanton2626N',
                    __b('Pizza Karton 26x26x4 D') => 'ug.countCanton2626D',
                    'ug.unitCanton2626D',
                    __b('Pizza Karton 26x26x4 S') => 'ug.countCanton2626S',
                    'ug.unitCanton2626S',
                    __b('Pizza Karton 26x26x4 H') => 'ug.countCanton2626H',
                    'ug.unitCanton2626H',
                    __b('Pizza Karton 28x28x4') => 'ug.countCanton2828',
                    'ug.unitCanton2828',
                    __b('Pizza Karton 32x32x4') => 'ug.countCanton3232',
                    'ug.unitCanton3232',
                    __b('Servietten 2lagig, 33x33') => 'ug.countServicing',
                    'ug.unitServicing',
                    __b('Plastiktüten') => 'ug.countBags',
                    'ug.unitBags',
                    __b('Chopsticks') => 'ug.countSticks',
                    'ug.unitSticks',
                    __b('BID') => 'b.id',
                    __b('BNR') => 'b.number',
                    __b('Betrag') => 'b.amount',
                    'b.status',
                    __b('Erstellt am') => 'ug.created'
                ))
                ->joinLeft(array('r' => 'restaurants'), 'r.id = ug.restaurantId', array())
                ->joinLeft(array('b' => 'billing'), "ug.id = b.refId AND b.mode = 'upselling_goods'", array())
                ->order("ug.id DESC");
        if ($status !== null) {
            $select->where("b.status = ?", $status);
        }

        // create grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn(__b('GID'), array('hidden' => 1));
        $grid->updateColumn(__b('BID'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2626'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2626N'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2626D'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2626S'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2626H'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton2828'), array('hidden' => 1));
        $grid->updateColumn(__b('unitCanton3232'), array('hidden' => 1));
        $grid->updateColumn(__b('unitServicing'), array('hidden' => 1));
        $grid->updateColumn(__b('unitBags'), array('hidden' => 1));
        $grid->updateColumn(__b('unitSticks'), array('hidden' => 1));
        $grid->updateColumn(__b('BNR'), array('title' => __b('Rechnungs Nummer')));
        $grid->updateColumn(__b('Pizza Karton 26x26x4'), array('decorator' => '{{'.__b('Pizza Karton 26x26x4').'}} x {{unitCanton2626}}'));
        $grid->updateColumn(__b('Pizza Karton 26x26x4 N'), array('decorator' => '{{'.__b('Pizza Karton 26x26x4 N').'}} x {{unitCanton2626N}}'));
        $grid->updateColumn(__b('Pizza Karton 26x26x4 D'), array('decorator' => '{{'.__b('Pizza Karton 26x26x4 D').'}} x {{unitCanton2626D}}'));
        $grid->updateColumn(__b('Pizza Karton 26x26x4 S'), array('decorator' => '{{'.__b('Pizza Karton 26x26x4 S').'}} x {{unitCanton2626S}}'));
        $grid->updateColumn(__b('Pizza Karton 26x26x4 H'), array('decorator' => '{{'.__b('Pizza Karton 26x26x4 H').'}} x {{unitCanton2626H}}'));
        $grid->updateColumn(__b('Pizza Karton 28x28x4'), array('decorator' => '{{'.__b('Pizza Karton 28x28x4').'}} x {{unitCanton2828}}'));
        $grid->updateColumn(__b('Pizza Karton 32x32x4'), array('decorator' => '{{'.__b('Pizza Karton 32x32x4').'}} x {{unitCanton3232}}'));
        $grid->updateColumn(__b('Servietten 2lagig, 33x33'), array('decorator' => '{{'.__b('Servietten 2lagig, 33x33').'}} x {{unitServicing}}'));
        $grid->updateColumn(__b('Plastiktüten'), array('decorator' => '{{'.__b('Plastiktüten').'}} x {{unitBags}}'));
        $grid->updateColumn(__b('Chopsticks'), array('decorator' => '{{'.__b('Chopsticks').'}} x {{unitSticks}}'));
        $grid->updateColumn(__b('Betrag'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{'.__b('Betrag').'}}'))));
        $grid->updateColumn(__b('Restaurant'), array('callback' => array('function' => 'getGridRestaurant', 'params' => array('{{'.__b('Restaurant').'}}', '{{'.__b('BID').'}}', '{{'.__b('BNR').'}}'))));
        $grid->updateColumn('status', array('title' => __b('Status'), 'class' => 'status', 'callback' => array('function' => 'billstatusToReadable', 'params' => array('{{status}}'))));
        // create grid extra column
        $gridOptions = new Bvb_Grid_Extra_Column();
        $gridOptions
                ->position('right')
                ->name(__b('Optionen'))
                ->decorator('<a href="/administration_upselling_goods/edit/id/{{'.__b('GID').'}}" target="_blank">'.__b("bearbeiten").'</a>');

        $grid->addExtraColumns($gridOptions);
        $this->view->grid = $grid->deploy();
        
        //list storage
        $products = new Yourdelivery_Model_Upselling_Storage();
        $this->view->products = $products->getProducts();
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2011
     */
    public function addAction() {

        // request
        $request = $this->getRequest();
        
        // create model
        $item = new Yourdelivery_Model_Upselling_Goods();
        
        // save
        if ($request->isPost()) {
            $post = $request->getPost();

            // create good
            $form = new Yourdelivery_Form_Administration_Upselling_Goods_Add();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                
                if ($values['countCanton2626'] && $values['costCanton2626'] < Yourdelivery_Model_Upselling_Goods::CANTON2626_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 26x26x4 ist nicht korrekt"));
                }
                elseif ($values['countCanton2626N'] && $values['costCanton2626N'] < Yourdelivery_Model_Upselling_Goods::CANTON2626N_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 26x26x4 Notebooksbilliger ist nicht korrekt"));
                }
                elseif ($values['countCanton2626D'] && $values['costCanton2626D'] < Yourdelivery_Model_Upselling_Goods::CANTON2626D_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 26x26x4 Discotel ist nicht korrekt"));
                }
                elseif ($values['countCanton2626S'] && $values['costCanton2626S'] < Yourdelivery_Model_Upselling_Goods::CANTON2626S_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 26x26x4 DeutschlandSIM ist nicht korrekt"));
                }
                elseif ($values['countCanton2626H'] && $values['costCanton2626H'] < Yourdelivery_Model_Upselling_Goods::CANTON2626H_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 26x26x4 Hannover ist nicht korrekt"));
                }
                elseif ($values['countCanton2828'] && $values['costCanton2828'] < Yourdelivery_Model_Upselling_Goods::CANTON2828_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 28x28x4 ist nicht korrekt"));
                }
                elseif ($values['countCanton3232'] && $values['costCanton3232'] < Yourdelivery_Model_Upselling_Goods::CANTON3232_PRICE) {
                    $this->error(__b("Der Preis für Pizza Karton 32x32x4 ist nicht korrekt"));
                }
                elseif ($values['countServicing'] && $values['costServicing'] < Yourdelivery_Model_Upselling_Goods::SERVICING_PRICE) {
                    $this->error(__b("Der Preis für Servietten 2lagig, 33x33 ist nicht korrekt"));
                }
                elseif ($values['countBags'] && $values['costBags'] < Yourdelivery_Model_Upselling_Goods::BAGS_PRICE) {
                    $this->error(__b("Der Preis für Plastiktüten ist nicht korrekt"));
                }
                elseif ($values['countSticks'] && $values['costSticks'] < Yourdelivery_Model_Upselling_Goods::CHOPSTICKS_PRICE) {
                    $this->error(__b("Der Preis für Chopsticks ist nicht korrekt"));
                }
                else {
                    $item->setData($values);
                    $item->setAdminId($this->session->admin->getId());

                    if ($item->calculateNetto() > 0) {
                        $item->save();
                    }
                }
            }
            
            // create bill
            if ($item->getId()) {
                $bill = new Yourdelivery_Model_Billing_Upselling_Goods();
                $bill->setUpsellingGoods($item);

                if (isset($post['sendWithVoucher'])) {
                    $voucher = Yourdelivery_Model_Billing::getVoucherAmounts($item->getService()->getId(), 2);
                    $bill->setVoucher($voucher);
                }

                if (!$bill->create()) {

                    $this->error(__b("Rechnung konnte nicht erstellt werden."));
                }
                elseif (isset($post['sendWithVoucher'])) {

                    return $this->_redirect('/administration_upselling_goods/vull/id/' . $item->getId());
                }
                elseif (isset($post['sendWithoutVoucher'])) {

                    return $this->_redirect('/administration_upselling_goods/send/id/' . $item->getId());
                }
            }

            $item->setData($post);
            $this->error($form->getMessages());
            $this->error(__b("Konnte nicht erstellt werden"));
        }

        // assign to view
        $this->view->item = $item;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2011
     */
    public function vullAction() {

        // request
        $request = $this->getRequest();
        $id = $request->getParam('id');
        
        // get item
        $item = $this->_getItem($id);
        if (!$item instanceof Yourdelivery_Model_Upselling_Goods) {
            $this->error(__b("Produkt nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }

        //
        $service = $item->getService();
        if (!$service instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return $this->_redirect('/administration_upselling_goods');
        }

        // check bill status
        $bill = $item->getBilling();
        if (!$bill instanceof Yourdelivery_Model_Billing_Upselling_Goods) {
            $this->error(__b("Rechnung nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }
        if ($bill->getStatus() == 4) {
            $this->error(__b("Rechnung wurde storniert und kann deshalb nicht bearbeitet werden."));
            return $this->_redirect('/administration_upselling_goods');
        }

        // save or send
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $bill->setStatus(2);
            if ($bill->save() && $bill->sendViaVullApi()) {
                $this->success(__b("Rechnung wurde erfolgreich zugeschickt."));
            }
            else {
                $this->error(__b("Rechnung konnte nicht verschickt werden."));
            }
            
            return $this->_redirect('/administration_upselling_goods');
        }

        // assign to view
        $this->view->item = $item;
        $this->view->service = $service;
        $this->view->bill = $bill;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2011
     */
    public function sendAction() {

        // request
        $request = $this->getRequest();
        $id = $request->getParam('id');
        
        // get item
        $item = $this->_getItem($id);
        if (!$item instanceof Yourdelivery_Model_Upselling_Goods) {
            $this->error(__b("Produkt nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }

        //
        $service = $item->getService();
        if (!$service instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return $this->_redirect('/administration_upselling_goods');
        }

        // 
        $bill = $item->getBilling();
        if (!$bill instanceof Yourdelivery_Model_Billing_Upselling_Goods) {
            $this->error(__b("Rechnung nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }
        if ($bill->getStatus() == 4) {
            $this->error(__b("Rechnung wurde storniert und kann deshalb nicht bearbeitet werden."));
            return $this->_redirect('/administration_upselling_goods');
        }
        $bill = new Yourdelivery_Model_Billing($bill->getId());
        
        // save or send
        if ($request->isPost()) {
            $post = $request->getPost();
            
            // send fax
            if ($post['via_fax'] == "1" && !empty($post['fax'])) {
                if ($bill->sendViaFax($post['fax'])) {
                    $this->success(__b("Erfolgreich per Fax verschickt"));
                }
                else {
                    $this->error(__b("Konnte nicht per Fax verschickt werden"));
                }
            }
            
            // send via email
            if ($post['via_email'] == "1" && !empty($post['email'])) {
                if ($bill->sendViaEmail($post['email'])) {
                    $this->success(__b("Erfolgreich per Email verschickt"));
                }
                else {
                    $this->error(__b("Konnte nicht per Email verschickt werden"));
                }    
            }
            
            // send via post
            if ($post['via_post'] == "1") {
                if ($bill->sendViaPost(true)) {
                    $this->success(__b("Erfolgreich per Post verschickt"));
                }
                else {
                    $this->error(__b("Konnte nicht per Post verschickt werden"));
                }     
            }
            
            return $this->_redirect('/administration_upselling_goods');
        }

        // assign to view
        $this->view->item = $item;
        $this->view->service = $service;
        $this->view->bill = $bill;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2011
     */
    public function editAction() {

        // request
        $request = $this->getRequest();
        $id = $request->getParam('id');
        
        // get item
        $item = $this->_getItem($id);
        if (!$item instanceof Yourdelivery_Model_Upselling_Goods) {
            $this->error(__b("Produkt nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }

        //
        $service = $item->getService();
        if (!$service instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return $this->_redirect('/administration_upselling_goods');
        }

        // 
        $bill = $item->getBilling();
        if (!$bill instanceof Yourdelivery_Model_Billing_Upselling_Goods) {
            $this->error(__b("Rechnung nicht gefunden."));
            return $this->_redirect('/administration_upselling_goods');
        }
        if ($bill->getStatus() == 4) {
            $this->error(__b("Rechnung wurde storniert und kann deshalb nicht bearbeitet werden."));
            return $this->_redirect('/administration_upselling_goods');
        }
        
        //
        if ($request->isPost()) {
            $post = $request->getPost();
            
            // storno
            if (isset($post['cancel'])) {
                if ($bill->getStatus() == 2) {
                    $this->error(__b("Rechnung konnte nicht storniert werden."));
                    return $this->_redirect('/administration_upselling_goods');
                }
                $bill->setStatus(4);
                if ($bill->save()) {
                    $this->success(__b("Rechnung wurde erfolgreich storniert."));
                    return $this->_redirect('/administration_upselling_goods');
                }
                
                $this->error(__b("Rechnung konnte nicht storniert werden."));
                return $this->_redirect('/administration_upselling_goods');
            }
            // pay
            elseif (isset($post['pay'])) {
                return $this->_redirect('/administration_upselling_goods/vull/id/' . $item->getId());
            }
                
        }
        
        // assign to view
        $this->view->item = $item;
        $this->view->service = $service;
        $this->view->bill = $bill;
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
