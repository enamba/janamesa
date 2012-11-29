<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
$lock = new ExclusiveLock('warehouse_daily');
if ($lock->lock()) {

    $warehouse = new Yourdelivery_Data_Warehouse();


    $ignore = array(
        'view_grid_orders',
        'view_sales'
    );

    $innodb = array();
    
    $db = Zend_Registry::get('dbAdapter');
    $views = $db->query("SHOW TABLES LIKE 'view_%'")->fetchAll();
    foreach ($views as $view) {
        $view = trim(current($view));

        if (in_array($view, $ignore)) {
            continue;
        }
        
        if(in_array($view, $innodb)) {
            $warehouse->setEngine("INNODB");
        }

        $warehouse->setView($view);
        try {
            $warehouse->regenerate();
            clog('info', 'regenerating data warehouse for view ' . $view);
        } catch (Exception $e) {
            clog('crit', 'Could not generate data warehouse for view ' . $view . ' Exception: ' . $e->getMessage());
        }
    }
} else {
    clog("warn", "could not get lock for " . __FILE__);
}

  