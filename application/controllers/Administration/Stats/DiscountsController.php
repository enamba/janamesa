<?php
/**
 * @author alex
 * @sinces 21.02.2011
 */
class Administration_Stats_DiscountsController extends Default_Controller_AdministrationBase{

    /**
     * statistics for discount actions
     * @author alex
     * @sinces 21.02.2011
     */
    public function indexAction(){
        $result = array();
        $resultSum = array();

        $from = '01.04.2009';
        $until = date("d.m.Y", time());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $from = $post['from'];
            $until = $post['until'];
        }
        
        foreach (Yourdelivery_Statistics_Overallstats::getUsedDiscounts(strtotime($from), strtotime($until), true) as $data) {
            if (!array_key_exists($data['year'], $result)) {
                $result[$data['year']] = array();
            }

            if (!array_key_exists($data['month'], $result[$data['year']])) {
                $result[$data['year']][$data['month']] = array();
                $resultSum[$data['year']][$data['month']] = 0;
            }

            if ( (strpos($data['rabattName'], '(Eingelöste Treuepunkte)') > 0) || (strpos($data['rabattName'], '(Treuepunkte einlösen)') > 0) ) {
                if (!array_key_exists('fidelity', $result[$data['year']][$data['month']])) {
                    $result[$data['year']][$data['month']]['fidelity']['name'] = 'Eingelöste Treuepunkte';
                    $result[$data['year']][$data['month']]['fidelity']['sum'] = 0;
                    $result[$data['year']][$data['month']]['fidelity']['count'] = 0;
                }

                $result[$data['year']][$data['month']]['fidelity']['sum'] += $data['sum'];
                $result[$data['year']][$data['month']]['fidelity']['count'] += $data['count'];
            }
            else {
                $result[$data['year']][$data['month']][$data['rabattId']]['name'] = $data['rabattName'];
                $result[$data['year']][$data['month']][$data['rabattId']]['sum'] = $data['sum'];
                $result[$data['year']][$data['month']][$data['rabattId']]['count'] = $data['count'];
            }

            $resultSum[$data['year']][$data['month']] += $data['sum'];
        }

        $this->view->assign('from', $from);
        $this->view->assign('until', $until);
        
        $this->view->assign('discounts_stats', $result);
        $this->view->assign('discounts_stats_sum', $resultSum);
    }

    /**
     * statistics for discounts
     * @author alex
     * @sinces 20.01.2011
     */
    public function codesAction(){
        $this->view->assign('discounts_today', Yourdelivery_Statistics_Overallstats::getUsedDiscounts(mktime(0, 0, 0), time()));

        $monday = strtotime('last Monday');
        if (date('N', time()) == 1) {
            $monday = mktime(0, 0, 0);
        }

        $this->view->assign('discounts_week', Yourdelivery_Statistics_Overallstats::getUsedDiscounts($monday, time()));
        $this->view->assign('discounts_month', Yourdelivery_Statistics_Overallstats::getUsedDiscounts(strtotime(date("Y-m-01 00:00:00", time())), time()));
    }
}
