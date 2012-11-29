<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
$lock = new ExclusiveLock('warehouse_minute');
if ($lock->lock()) {
    $warehouse = new Yourdelivery_Data_Warehouse();

    $warehouse->setView('view_grid_orders_this_day');
    try {
        if ($warehouse->regenerate()) {
            clog('info', 'CRONJOB: regenerating data warehouse for view view_sales');
        } else {
            clog('crit', 'CRONJOB: Could not generate data warehouse for view view_sales');
        }
    } catch (Exception $e) {
        clog('crit', 'CRONJOB: Could not generate data warehouse for view view_sales');
    }

} else {
    clog("warn", "could not get lock for " . __FILE__);
}
        


