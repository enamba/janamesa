<?php
/**
 * @author alex
 * @sinces 29.11.2010
 */
class Administration_Stats_SalespersonsController extends Default_Controller_AdministrationBase{

    /**
     * Show table with all contract, made in the defined time slot
     * @author alex
     * @sinces 29.11.2010
     */
    public function indexAction(){

        // show default time slot of one week
        $from  = date('d.m.Y', time() - 7*24*60*60);
        $until = date('d.m.Y');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post  = $request->getPost();
            $from  = $post['fromD'];
            $until = $post['untilD'];
        }

        // if time slot was defined, convert it in sql time format
        $fromFormatted  = date(DATE_DB, strtotime($from)) . ' 00:00:00';
        $untilFormatted = date(DATE_DB, strtotime($until)) . ' 23:59:59';
        
        $countAll = 0;
        $salespersons = array();
        
        $rows = Yourdelivery_Model_DbTable_Salesperson_Restaurant::getContracts($fromFormatted, $untilFormatted);
        foreach ($rows as $row) {
            if (!array_key_exists($row['salespersonId'], $salespersons)) {
                $salespersons[$row['salespersonId']]= array(
                    'salespersonName' => $row['salespersonName'],
                    'salespersonPrename' => $row['salespersonPrename'],
                    'salespersonCallcenter' => $row['salespersonCallcenter'],
                    'salespersonSalary' => $row['salespersonSalary'],
                    'Categoria' => $row['categoria'],
                    'count' => 0,
                    'noContractCount' => 0,
                    'data' => array()
                );
            }

            $salespersons[$row['salespersonId']]['data'][] = $row;

            $salespersons[$row['salespersonId']]['count']++;
            if ($row['hasContract'] == 0) {
                $salespersons[$row['salespersonId']]['noContractCount']++;                
            }
            
            $countAll++;
        }

        $this->view->offlineStati = Yourdelivery_Model_Servicetype_Abstract::getStati();
        $this->view->salespersons = $salespersons;
        $this->view->countAll = $countAll;
        $this->view->from = $from;
        $this->view->until = $until;
        
    }

}
