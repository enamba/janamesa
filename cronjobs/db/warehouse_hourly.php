<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
$lock = new ExclusiveLock('warehouse_hourly');
if ($lock->lock()) {
    $warehouse = new Yourdelivery_Data_Warehouse();

    $warehouse->setView('view_sales');
    try {
        $warehouse->regenerate();
        clog('info', 'CRONJOB: regenerating data warehouse for view view_sales');
    } catch (Exception $e) {
        clog('crit', 'CRONJOB: Could not generate data warehouse for view view_sales');
    }
} else {
    clog("warn", "could not get lock for " . __FILE__);
}


