<?php
/**
 * @author alex
 * @sinces 18.11.2010
 */
class Administration_Stats_CompaniesController extends Default_Controller_AdministrationBase{

    /**
     * @author mlaug
     * @since 06.04.2011
     */
    public function indexAction(){
        $dbTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->account_per_company = $dbTable->getAccountsPerCompany();
        $this->view->orders_per_company_per_employee = $dbTable->getOrderPerCompanyPerEmployee();
        $this->view->average_bucket_value = $dbTable->getAverageBucketValue();
    }

    /**
     * @author alex
     * @sinces 18.11.2010
     */
    public function conversionAction(){      
        //// get parameter
        $request = $this->getRequest();
        $companyIds = $request->getParam('companyIds');

        // companies
        $dbTable = new Yourdelivery_Model_DbTable_Company();
        $companies = $dbTable->getDistinctNameId();
        $this->view->companies = $companies;

        if (is_null($companyIds) || count($companyIds)==0) {
            return;
        }

        $year = $request->getParam('year');

        $result = array();

        foreach ($companyIds as $companyId) {
            $compresult = array();
            
            // get db
            $db = Zend_Registry::get('dbAdapter');
            
            // get company name
            $cname = $db->fetchRow("SELECT name FROM `companys` WHERE id = " . $companyId);

            $compresult['id'] = $companyId;
            $compresult['name'] = $cname['name'];

            // employees
            $employees = array();
            for ($m = 1; $m < 13; $m++) {
                $employees[$m] = $db->fetchOne(
                    "SELECT COUNT(c.id)
                    FROM `customers` c
                    INNER JOIN `customer_company` cc ON c.id = cc.customerId
                        AND cc.companyId = ?
                    WHERE c.created < ?", array($companyId, date(DATE_DB, mktime(0, 0, 0, ($m + 1 > 12 ? 1 : $m + 1), 1, ($m + 1 > 12 ? $year + 1 : $year))))
                );
            }
            $compresult['employees'] = $employees;

            // sales
            $rows = $db->fetchAll(
                "SELECT c.name `company`, MONTH(o.time) `monat`,
                    COUNT(o.orderId) `count`, SUM(o.sales) `sales`
                FROM `data_view_sales` o
                INNER JOIN `companys` c ON o.companyId = c.id
                    AND c.id = ?
                WHERE YEAR(o.time) = ?
                GROUP BY `monat`
                ORDER BY `monat`", array($companyId, $year)
            );
            $sales = array();
            foreach ($rows as $row) {
                $sales[$row['monat']] = array(
                    'count' => $row['count'],
                    'sales' => $row['sales'],
                );
            }
            $compresult['sales'] = $sales;

            // sales details
            $rows = $db->fetchAll(
                "SELECT c.name `company`, o.mode, MONTH(o.time) `monat`,
                    COUNT(o.orderId) `count`, SUM(o.sales) `sales`
                FROM `data_view_sales` o
                INNER JOIN `companys` c ON o.companyId = c.id
                    AND c.id = ?
                WHERE YEAR(o.time) = ?
                GROUP BY o.mode, `monat`
                ORDER BY o.mode, `monat`", array($companyId, $year)
            );
            $details = array();
            foreach ($rows as $row) {
                if (!is_array($details[$row['mode']])) {
                    $details[$row['mode']] = array();
                }
                $details[$row['mode']][$row['monat']] = array(
                    'count'   => $row['count'],
                    'sales'   => $row['sales'],
                    'percent' => round($row['count'] / $sales[$row['monat']]['count'] * 100, 2),
                );
            }
            $compresult['details'] = $details;

            // budgets
            $rows = $db->fetchAll(
                "SELECT c.name `company`, MONTH(o.time) `monat`,
                    COUNT(ocg.id) `count`, SUM(ocg.amount) `amount`
                FROM `orders` o
                INNER JOIN `companys` c ON o.companyId = c.id
                    AND c.id = ?
                INNER JOIN `order_company_group` ocg ON o.id = ocg.orderId
                WHERE o.state > 0
                    AND YEAR(o.time) = ?
                GROUP BY `monat`
                ORDER BY `monat`", array($companyId, $year)
            );
            $budgets = array();
            foreach ($rows as $row) {
                $budgets[$row['monat']] = array(
                    'count'   => $row['count'],
                    'amount'  => $row['amount'],
                    'percent' => $employees[$row['monat']] > 0 ? round($row['count'] / ($employees[$row['monat']] * 22) * 100, 2) : 0,
                    'sales'   => $row['count'] > 0 ? round($sales[$row['monat']]['sales'] / $row['count'], 2) : 0,
                );
            }
            $compresult['budgets'] = $budgets;

            $result[] = $compresult;
        }

        $this->view->result = $result;

        // modes
        $this->view->modes = array(
            'rest'    => 'Restaurant',
            'cater'   => 'Catering',
            'fruit'   => 'Obst',
            'great'   => 'Großhandel',
            'canteen' => 'Kantine',
        );

    }

}
