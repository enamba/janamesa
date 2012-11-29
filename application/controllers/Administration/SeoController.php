<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * tracing codes and landing pages management
 *
 * @author alex
 */
class Administration_SeoController extends Default_Controller_AdministrationBase {

    public function  preDispatch() {
        parent::preDispatch();
        $this->view->assign('navseo', 'active');
    }

    /**
     * show a summary of tracking codes staistics
     * @author alex
     */
    public function trackingsummaryAction(){
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (!is_null($request->getParam('track_eval', null)) ) {
                $sum = 0;
                foreach($request->getPost() as $k=>$id) {
                    if (strpos($k, '_code') != 0) {
                        $sum += Yourdelivery_Model_Tracking_Code::calculateOrdersSum($id);
                    }
                }
                $this->view->assign('eval_sum', $sum);
            }
            else if (!is_null($request->getParam('time_eval', null)) ) {
                $post = $request->getPost();

                $start_sel =  strtotime($post['startTimeShortD']);
                $end_sel =  strtotime($post['endTimeShortD']);

                $sum = Yourdelivery_Model_Tracking_Code::calculateOrdersSumOverTime($start_sel, $end_sel);
                $this->view->assign('time_sum', $sum);
            }
        }
//
//
//        $codes = Yourdelivery_Model_Tracking_Code::all();
//        $this->view->assign('codes',$codes);

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $showdeleted = $this->session->showdeletedusers;

        $grid->setExport(array());
        $grid->setPagination(50);
        //select orders
        $select = $db->select()->from(array('tc'=>'tracking_code'),
                                            array(
                                                __b('ID') => 'id',
                                                __b('Name') => 'name',
                                                __b('Beschreibung') => 'desc',
                                                __b('PostFix') =>'postfix',
                                                __b('Redirect') =>'redirect',
                                            ))
                     ->joinLeft(array('tcc'=>'tracking_campaign'),'tc.campaignId=tcc.id',
                             array(__b('Kampagne') =>'tcc.name',
                                   __b('Kamp.-Beschreibung') => 'tcc.desc'
                                 ))
                     ->order('tc.id ASC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'),array('decorator'=>'#{{'.__b('ID').'}}'));

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name('Options')->decorator('
            <div>
                <a href=\"#\" class=\"yd-trackingcode-details\" id=\"{{'.__b('ID').'}}\">'. __b("Details") . '</a><br />
                <a href=\"/administration_seo/codeedit/id/{{'.__b('ID').'}}\">'. __b("Editieren") . '</a>
            </div>');
        //add extra rows
        $grid->addExtraColumns($option);

        $option = new Bvb_Grid_Extra_Column();
        $option->position('left')->name('Options')->decorator("
            <input value=\"{{".__b('ID')."}}\" type=\"checkbox\" id=\"code_id\" name=\"track_code{{".__b('ID')."}}\" />
                ");
        //add extra rows
        $grid->addExtraColumns($option);




        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__b('Name'))
                ->addFilter(__b('Beschreibung'))
                ->addFilter(__b('PostFix'))
                ->addFilter(__b('Redirect'))
                ->addFilter(__b('Kampagne'))
                ->addFilter(__b('Kamp.-Beschreibung'));
                
        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }
}
?>
