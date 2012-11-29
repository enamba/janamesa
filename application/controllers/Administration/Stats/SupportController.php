<?php

/**
 * @author vpriem
 * @sinces 04.11.2010
 */
class Administration_Stats_SupportController extends Default_Controller_AdministrationBase {

    /**
     * Initialize
     */
    public function init() {

        parent::init();

        $from_date = date_create();
        $from_date->modify("-1 month");
        $from = $from_date->format("d.m.Y");
        $until = date('d.m.Y');

        if (empty($this->session_admin->stats_support_from)) {
            $this->session_admin->stats_support_from = $from;
        }

        if (empty($this->session_admin->stats_support_until)) {
            $this->session_admin->stats_support_until = $until;
        }
    }

    /**
     * @author vpriem
     * @modified daniel
     * @since 07.10.2011
     */
    public function indexAction() {

        $actions = Yourdelivery_Model_Admin_Access_Tracking::getActions();

        $stats = array();
        $tracking = new Yourdelivery_Model_Admin_Access_Tracking();
        $request = $this->getRequest();
        $export = $request->getParam("export", false);
        $until = $this->session_admin->stats_support_until;
        $from = $this->session_admin->stats_support_from;
        $groups = $this->session_admin->stats_support_groups;
        if(!is_array($groups)){
            $groups = false;
        }
        
        
        if ($request->isPost()) {

            $from = $request->getParam('from');
            $until = $request->getParam('until');
            $groups = $request->getParam('groups',false);                        
            $this->session_admin->stats_support_from = $from;
            $this->session_admin->stats_support_until = $until;
            $this->session_admin->stats_support_groups = $groups;
        }
        
        try {

            $_stats = $tracking->getStats($from, $until,false,$groups);
        } catch (Exception $e) {

            $this->error(__b("Die Datumsangabe ist ungültig!"));
        }


        $stats = $this->_prepareStats($_stats);
              
        $this->view->groups = is_array($groups) ? array_values($groups) : $groups;
        $this->_setStatsView($stats, $actions, $from, $until, $id = false, $export);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     * 
     */
    public function userAction() {
        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            return $this->_redirect("administration_stats_support");
        }

        $actions = Yourdelivery_Model_Admin_Access_Tracking::getActions();
        $tracking = new Yourdelivery_Model_Admin_Access_Tracking();

        $until = $this->session_admin->stats_support_until;
        $from = $this->session_admin->stats_support_from;

        $request = $this->getRequest();
        $export = $request->getParam("export", false);
        if ($request->isPost()) {

            $from = $request->getParam('from');
            $until = $request->getParam('until');
            $this->session_admin->stats_support_from = $from;
            $this->session_admin->stats_support_until = $until;
        }

        try {

            $_stats = $tracking->getStats($from, $until, $id);
        } catch (Exception $e) {


            $this->error(__b("Die Datumsangabe ist ungültig!"));
        }

        $stats = $this->_prepareStats($_stats, $from, $until);

        $this->_setStatsView($stats, $actions, $from, $until, $id, $export);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 11.10.2011
     * @param array $_stats
     * @return array 
     */
    protected function _prepareStats($_stats) {


        foreach ($_stats as $s) {
            if (!is_array($stats[$s['adminId']])) {
                $stats[$s['adminId']] = array();
                $stats[$s['adminId']]['name'] = $s['name'];
            }
            $stats[$s['adminId']][$s['action']] = $s['count'];
        }
        return $stats;
    }

    /**
     * Render Statistics as html or CSV, for user and index
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     * @param array $stats
     * @param array $actions
     * @param string $from
     * @param string $until
     * @param int $id
     * @param string $export
     * @return void
     */
    protected function _setStatsView($stats, $actions, $from, $until, $id=false, $export =false) {


        $this->view->actions = $actions;
        $this->view->stats = $stats;
        $this->view->from = $from;
        $this->view->until = $until;
        $this->view->url = "/" . $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName();

        if ($id) {

            $tracking = new Yourdelivery_Model_Admin_Access_Tracking();
            $userStats = $tracking->getUserStats($id, $from, $until);
            $this->view->userStats = $userStats;
            $this->view->id = $id;
            $this->view->url = "/" . $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName() . "/id/" . $id;
        }

        if ($export === "csv") {
            $this->_disableView();
          
            $csv = new Default_Exporter_Csv();
            $csv->filename = "support_kpi_".$from."-".$until.".csv";

            $csv->addCol('Aktionen ' . $from . " - " . $until);
            foreach ($stats as $supporter => $stat) {
                $csv->addCol($stat['name'] . " (id: " . $supporter . ")");
            }

            if ($id && !is_null($userStats)) {
                $csv->addCol("ids");
                $csv->filename = "support_kpi_id_".$id."_".$from."-".$until.".csv";
            }

            foreach ($actions as $a => $action) {
                $row = array();
                $row[] = $action;
                foreach ($stats as $supporter => $stat) {
                    if (!empty($stat[$a])) {
                        $row[] = $stat[$a];
                    } else {
                        $row[] = 0;
                    }
                }

                if (!is_null($userStats)) {
                    $ids_string = "";
                    if (count($userStats[$a]) > 0) {
                        $ids = $userStats[$a];
                        $ids_string .= $ids['modelType'];
                        unset($ids['modelType']);
                        $ids_string .= ": " . implode(",", $ids);
                    }
                    $row[] = $ids_string;
                }

                $csv->addRow($row);
            }

            $response = $this->getResponse();
            $csv->save($response);
            return;
        } else {

            $this->render("index");
        }
    }

}
