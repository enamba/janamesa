<?php

/**
 * Description of MarketingController
 * @author mlaug
 * @since 04.04.2011
 */
class Administration_Stats_MarketingController extends Default_Controller_AdministrationBase {

    /**
     * @author mlaug
     * @since 08.06.2011
     */
    public function indexAction() {
        
    }
    
    public function segmentAction(){
        $request = $this->getRequest();
        if ( $request->isPost() ){          
            $piwik = new Piwik_Segmentation();
            
            $data = $request->getParam('data');
            switch($data){
                default:
                    die(__b("no data type defined"));
                case 'referers':
                    $piwik->setMethod('Referers', 'getRefererType');
                    break;
                case 'actions':
                    $piwik->setMethod('Actions','getPageUrls');
                    break;
                case 'custom':
                    $piwik->setMethod('CustomVariables','getVustomVariables');
                    break;
                case 'goals':
                    $piwik->setMethod('Goals', 'getGoals');
                    break;
            }
            
            //add segments
            $segment = (array) $request->getParam('segment',array());
            $compare = (array) $request->getParam('compare',array());
            $value = (array) $request->getParam('value',array());
            $join = (array) $request->getParam('join',array());
            for($i=0;$i<count($segment);$i++){
                $_segment = array_pop($segment);
                $_compare = array_pop($compare);
                $_value = array_pop($value);
                $_join = array_pop($join);
                $piwik->addSegment($_segment, $_value, $_compare, $_join);
            }
            
            $this->view->segmentation = $piwik->getHtml();
        }
        else{
            return $this->_redirect('/administration_stats_marketing');
        }
    }

    /**
     * generate a link, which can be used to refer to a certian sale channel
     * @author mlaug
     * @since 29.04.2011
     */
    public function linkAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->view->post = $request->getPost();
          
            //save salechannel to database
            $table = new Yourdelivery_Model_DbTable_Marketing_Url();
            $id = $table->createRow(
                    array_merge(
                            $request->getPost(),
                            array(
                                'url' => $data
                            )))->save();
            
            
            $this->view->link = sprintf('http://www.%s?yd_com=%s', $this->config->domain->base, $id);
            
        } else {
            $this->view->link = null;
        }
    }

    /**
     * @author mlaug
     * @since 03.05.2011
     */
    public function exportAction() {
        
    }

    /**
     * show a sortable, filterable table of all entries in the table 'salechannel_cost'
     */
    public function costsAction() {
        $db = Zend_Registry::get('dbAdapter');

        //select entries
        $select = $db->select()->from(array('sc'=>'salechannel_cost'),
                                            array(
                                                'ID'        => 'id',
                                                'saleChannel',
                                                'subSaleChannel',
                                                __b('Kosten')    => 'cost',
                                                __b('Name')    => 'name',
                                                __b('Von')       => 'from',
                                                __b('Bis')       => 'until'
                                            ))
                     ->order('sc.id DESC');

        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'dateYMD', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'dateYMD', 'params' => array('{{' . __b('Bis') . '}}'))));
        $grid->updateColumn(__b('Kosten'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Kosten') . '}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();

        //add filters
        $filters->addFilter('ID')
                ->addFilter('saleChannel')
                ->addFilter('subSaleChannel');

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Optionen'))->decorator(
                '<div>
                        <a href=\"/administration_stats_marketing/editcost/costId/{{ID}}\">'.__b("Editieren").'</a><br />
                        <a href=\"/administration_stats_marketing/deletecost/costId/{{ID}}\" onclick=\"javascript:return confirm('.__b("Vorsicht!! Soll dieser Kosteneintrag wirklich gel&ouml;scht werden?").')\">'.__b("L&ouml;schen").'</a>
                    </div>'
        );
        
        $download = new Bvb_Grid_Extra_Column();
        $download->position('right')->name('Report')->decorator(
                '<div>
                        <a href=\"/administration_stats_marketing/export/id/{{ID}}/group/daily\">'.__b("Report pro Tag").'</a><br />
                        <a href=\"/administration_stats_marketing/export/id/{{ID}}/group/weekly\">'.__b("Report je Kalenderwoche").'</a><br />
                        <a href=\"/administration_stats_marketing/export/id/{{ID}}/group/monthly\">'.__b("Report je Monat").'</a>
                    </div>'
        );


        //add extra rows
        $grid->addExtraColumns($option, $download);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * create new salechannel cost entry
     * @author alex
     * @since 03.05.2011
     */
    public function createcostAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_stats_marketing/costs');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            //create new salechannel cost entry
            $form = new Yourdelivery_Form_Administration_Salechannel_Createcost();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                if (($values['fromTimeD'] > $values['untilTimeD']) ||
                        (($values['fromTimeD'] == $values['untilTimeD']) && ($values['fromTimeT'] > $values['untilTimeT']))) {
                    $this->error(__b("Wrong time parameter!"));
                    $this->_redirect('/administration_stats_marketing/createcost');
                }

                $values['from'] = substr($values['fromTimeD'], 6, 4) . "-" . substr($values['fromTimeD'], 3, 2) . "-" . substr($values['fromTimeD'], 0, 2) . " " . substr($values['fromTimeT'], 0, 2) . ":" . substr($values['fromTimeT'], 3, 2) . ":00";
                $values['until'] = substr($values['untilTimeD'], 6, 4) . "-" . substr($values['untilTimeD'], 3, 2) . "-" . substr($values['untilTimeD'], 0, 2) . " " . substr($values['untilTimeT'], 0, 2) . ":" . substr($values['untilTimeT'], 3, 2) . ":00";

                $values['cost'] = priceToInt2($values['cost']);

                $salechannelcost = new Yourdelivery_Model_Salechannel_Cost();
                $salechannelcost->setData($values);
                $salechannelcost->save();

                $this->success(__b("Salechannel cost entry was succesfully created"));
                $this->logger->adminInfo(sprintf("Created salechannel cost entry  #%d", $salechannelcost->getId()));
                $this->_redirect('/administration_stats_marketing/costs');
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('distinctSalechannels', Yourdelivery_Model_DbTable_Salechannel_Cost::getDistinctSalechannels());
        $this->view->assign('distinctSubsalechannels', Yourdelivery_Model_DbTable_Salechannel_Cost::getDistinctSubsalechannels());
    }

    /**
     * edit new salechannel cost entry
     * @author alex
     * @since 03.05.2011
     */
    public function editcostAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_stats_marketing/costs');
        }

        if (is_null($request->getParam('costId'))) {
            $this->error(__b("This salechannel cost entry ist non-existant"));
            $this->_redirect('/administration_stats_marketing/costs');
        }

        //create salechannel cost entry object
        try {
            $salechannelcost = new Yourdelivery_Model_Salechannel_Cost($request->getParam('costId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("This salechannel cost entry ist non-existant"));
            $this->_redirect($path);
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Salechannel_Editcost();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                if (($values['fromTimeD'] > $values['untilTimeD']) ||
                        (($values['fromTimeD'] == $values['untilTimeD']) && ($values['fromTimeT'] > $values['untilTimeT']))) {
                    $this->error(__b("Wrong time parameter!"));
                    $this->_redirect('/administration_stats_marketing/editcost/costId/' . $salechannelcost->getId());
                }

                /**
                 * @todo: extract into a helper
                 */
                $values['from'] = substr($values['fromTimeD'], 6, 4) . "-" . substr($values['fromTimeD'], 3, 2) . "-" . substr($values['fromTimeD'], 0, 2) . " " . substr($values['fromTimeT'], 0, 2) . ":" . substr($values['fromTimeT'], 3, 2) . ":00";
                $values['until'] = substr($values['untilTimeD'], 6, 4) . "-" . substr($values['untilTimeD'], 3, 2) . "-" . substr($values['untilTimeD'], 0, 2) . " " . substr($values['untilTimeT'], 0, 2) . ":" . substr($values['untilTimeT'], 3, 2) . ":00";

                $values['cost'] = priceToInt2($values['cost']);

                //save new data
                $salechannelcost->setData($values);
                $salechannelcost->save();

                $this->success(__b("Changes successfully saved"));
                $this->logger->adminInfo(sprintf("Salechannel cost entry #%d was edited", $salechannelcost->getId()));
                $this->_redirect('/administration_stats_marketing/costs');
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('salechannelcost', $salechannelcost);
        $this->view->assign('distinctSalechannels', Yourdelivery_Model_DbTable_Salechannel_Cost::getDistinctSalechannels());
        $this->view->assign('distinctSubsalechannels', Yourdelivery_Model_DbTable_Salechannel_Cost::getDistinctSubsalechannels());
    }

    /**
     * delete salechannel cost
     * @author alex
     * @since 03.05.2011
     */
    public function deletecostAction() {
        $request = $this->getRequest();
        $costId = $request->getParam('costId');

        //create cost object to test if it exists
        if (!is_null($costId)) {
            $cost = new Yourdelivery_Model_Salechannel_Cost($costId);

            if (is_null($cost->getId())) {
                $this->error(__b("Cannot find this cost entry!"));
            } else {
                $cost->getTable()->remove($cost->getId());
                $this->logger->adminInfo(sprintf("Salechannel cost #%d was deleted", $cost->getId()));
                $this->success(__b("Cost entry successfully deleted"));
            }
        } else {
            $this->error(__b("Cannot find this cost entry!"));
        }

        $this->_redirect('/administration_stats_marketing/costs');
    }

}
