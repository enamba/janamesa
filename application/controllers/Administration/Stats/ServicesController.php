<?php

/**
 * @author vpriem
 * @sinces 04.11.2010
 */
class Administration_Stats_ServicesController extends Default_Controller_AdministrationBase {

    /**
     * @author vpriem
     * @sinces 04.11.2010
     */
    public function indexAction() {

       // get db
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $config = Zend_Registry::get('configuration');

        // restaurants statistics
        $services = $db->fetchAll(
                $db->select()->from(array('r' => 'data_view_restaurants_per_servicetype_and_plz'), array('sum' => new Zend_Db_Expr('SUM(r.count)'),
                    'category' => 'r.category',
                    'categoryName' => 'r.categoryName',
                    'isOnline' => 'r.isOnline'
                        )
                )->group(array('category', 'isOnline'))
        );

        $servicesPerCategory = array();
        foreach ($services as $s) {
            $servicesPerCategory[$s['categoryName']][$s['isOnline']] = $s['sum'];
        }

        $this->view->servicesPerCategory = $servicesPerCategory;

        // premium services statistics 
        $select = $db->select()->from(array('r' => 'restaurants'), array('online' => new Zend_Db_Expr('SUM(r.isOnline)'),
                            'offline' => new Zend_Db_Expr('SUM(!r.isOnline)'),
                            'count' => new Zend_Db_Expr('COUNT(r.id)')
                                )
                        )
                        ->where('r.deleted = 0');
        
        if($config->domain->base == 'janamesa.com.br') {
            $select->where('r.franchiseTypeId = 3');
        } else {
            $select->where('r.franchiseTypeId in (3, 4, 5)');
        }
                        
        $premium = $db->fetchAll($select);
                
        $this->view->premium = $premium;

        $noContract = $db->fetchRow($db->select()->from(array('r' => 'restaurants'), array('online' => new Zend_Db_Expr('SUM(r.isOnline)'),
                            'offline' => new Zend_Db_Expr('SUM(!r.isOnline)'),
                            'count' => new Zend_Db_Expr('COUNT(r.id)')
                                )
                        )
                        ->where('r.deleted = 0')
                        ->where('r.franchiseTypeId = 2')
        );

        $this->view->noContract = $noContract;

        // restaurants accepting only cash and online too
        $servicesOnlyCash = $db->fetchAssoc(
                $db->select()->from(array('r' => 'restaurants'), array('isOnline' => 'r.isOnline',
                            'onlyCash' => new Zend_Db_Expr('SUM(r.onlyCash)'),
                            'notOnlyCash' => new Zend_Db_Expr('SUM(!r.onlyCash)')
                                )
                        )
                        ->where('r.deleted = 0')
                        ->group('isOnline')
                        ->order('isOnline')
        );
        $this->view->servicesOnlyCash = $servicesOnlyCash;

        // all restaurants online and offline
        $servicesGlobal = $db->fetchAssoc(
                $db->select()->from(array('r' => 'data_view_restaurants_count_per_plz'), array('isOnline' => 'r.isOnline',
                            'sum' => new Zend_Db_Expr('SUM(r.count)')
                                )
                        )
                        ->group('isOnline')
        );
        $this->view->servicesGlobal = $servicesGlobal;
    }

    /**
     * restaurants statistics by city
     * @author alex
     * @sinces 03.01.2011
     */
    public function restaurantspercityAction() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        // restaurants per cities statistics
        $results = $db->fetchAll(
                "SELECT c.city `city`, s.className `servicetype`, rb.restaurants,
                SUM(r.isOnline) `online`, SUM(!r.isOnline) `offline`,
                SUM(IF(r.isOnline = 0, IF(r.status = 11, 1, 0), 0)) `canceled`,
                COUNT(r.id) `count`
            FROM `restaurants` r
            INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
            INNER JOIN `servicetypes` s ON rs.servicetypeId = s.id
            INNER JOIN `city` c ON r.cityId = c.id
            LEFT JOIN `restaurant_benchmark` rb ON c.city = rb.city
            WHERE r.deleted = 0
            GROUP BY c.city, s.id
            ORDER BY c.city"
        );

        $cities = array();
        foreach ($results as $res) {
            if (!is_array($cities[$res['city']])) {
                $cities[$res['city']] = array();
            }

            $cities[$res['city']]['restaurants'] = $res['restaurants'];
            $cities[$res['city']][$res['servicetype']] = array(
                'online' => $res['online'],
                'offline' => $res['offline'],
                'canceled' => $res['canceled'],
                'count' => $res['count'],
            );
        }

        $this->view->cities = $cities;
    }

    /**
     * restaurants statistics by plz and category
     * @author alex
     * @sinces 03.01.2011
     */
    public function restaurantsperplzAction() {
        // get db
        $db = Zend_Registry::get('dbAdapter');

        // count of restaurants of different types per plz
        $restaurants = $db->fetchAll("select * from data_view_restaurants_per_servicetype_and_plz");

        $restaurantsPerPlz = array();
        $restaurantsGlobalSum[] = array();

        for ($i = 0; $i <= 9; $i++) {
            $restaurantsPerPlz[$i] = array();
        }

        foreach ($restaurants as $res) {
            $restaurantsPerPlz[$res['r_plz']][$res['category']][$res['isOnline']] = $res['count'];
            $restaurantsGlobalSum[$res['category']][$res['isOnline']] += $res['count'];
        }

        // count of restaurants per plz
        $restaurantsSum = $db->fetchAll("select * from data_view_restaurants_count_per_plz");

        foreach ($restaurantsSum as $res) {
            $restaurantsPerPlz[$res['r_plz']]['sum'][$res['isOnline']] = $res['count'];
            $restaurantsGlobalSum['sum'][$res['isOnline']] += $res['count'];
        }

        $this->view->restaurantsPerPlz = $restaurantsPerPlz;
        $this->view->restaurantsSumStats = $restaurantsGlobalSum;
    }

    /**
     * restaurants statistics by status
     * @author alex
     * @modified daniel
     * @sinces 03.01.2011
     */
    public function restaurantsperstatusAction() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        // restaurants per cities statistics
        
        $select = $db->select()->from('restaurants', array('status' => 'status', 
                                                                                        'count' => new  Zend_Db_Expr("count(id)"),
                                                                                        'countContract' => new  Zend_Db_Expr("sum(if(franchiseTypeId != 2 , 1 , 0))"),
                                                                                        'countNoContract' => new  Zend_Db_Expr("sum(if(franchiseTypeId = 2, 1 , 0))")
                                                                                        ))
                                               ->where('deleted = 0')
                                               ->group('status')
                                               ->order('status ASC');
        
        $results = $db->fetchAll($select);
        //$results = $db->fetchAll("select status, count(id) as count from restaurants where deleted=0 group by status order by status");


        $history_week = $db->query($db->select()
                                ->from('restaurant_status_history', array('day' => 'WEEKDAY(created)',
                                    'status' => 'status',
                                    'created' => 'created',
                                    'delCount' => 'delCount',
                                    'addCount' => 'addCount'))
                                ->where("WEEK(created,1) = WEEK(NOW(),1) AND YEAR(created) = YEAR(NOW())")                       
                )->fetchAll();
        

        
        
        //last Sunday
        $day = array();
        $status_days = array();

        for ($i = 0; $i < 7; $i++) {
            $day = array();
            foreach ($history_week as $entry_day) {
                if ($entry_day['day'] == $i) {
                    $day[$entry_day['status']] = array('add' => $entry_day['addCount'], 'sub' => $entry_day['delCount']);
                }
            }
            $status_days[$i] = $day;
        }



        $statis = Yourdelivery_Model_Servicetype_Abstract::getStati();
        $currentDay = (date('N') - 1);

        $restaurants = array();
        foreach ($results as $res) {
            $status = $statis[intval($res['status'])];
            $diff = array();
            $sumAdd = 0;
            $sumSub = 0;
            for ($i = 0; $i < 7; $i++) {                
                $diff[$i] = $status_days[$i][$res['status']];
                $sumAdd  += $status_days[$i][$res['status']]['add'];
                $sumSub  += $status_days[$i][$res['status']]['sub'];                
            }
            $diff[7] = array("add" => $sumAdd, "sub" => $sumSub);

            $elem = array('count' => $res['count'], 'countContract' => $res['countContract'],'countNoContract' => $res['countNoContract'],     'statusId' => $res['status'], 'status' => $status, 'diff' => $diff);
            $restaurants[] = $elem;
        }      
        
        $this->view->restaurants = $restaurants;
    }
    
    public function restaurantsperkommAction() {
        
          $db = Zend_Registry::get('dbAdapter');
          
          $select = $db->select()
                                ->from('restaurants', 
                                        array(
                                                'provision' => 'komm', 
                                                'count' => new  Zend_Db_Expr("count(id)"),
                                                'countContract' => new  Zend_Db_Expr("sum(if(franchiseTypeId != 2 , 1 , 0))"),
                                                'countNoContract' => new  Zend_Db_Expr("sum(if(franchiseTypeId = 2, 1 , 0))"))
                                        )
                                ->where('isOnline = 1')
                                ->group('provision')
                    ;
          
          
          $services = $db->fetchAll($select);
          
          $this->view->services = $services;
          
    }
    
}
