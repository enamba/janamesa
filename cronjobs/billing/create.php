<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

ini_set('memory_limit', '16384M');
ini_set('max_execution_time', 0);

$countCompany = array(0, 0);
$countRestaurant = array(0, 0);
$countCourier = array(0, 0);

/**
 * create bill for companies
 */
foreach (Yourdelivery_Model_Company::all() as $company) {

    $create = false;
    switch ($company->getBillInterval()) {

        case Yourdelivery_Model_Billing::BILL_PER_TRANSACTION:
            clog('info', sprintf('creating bill per transaction for %s (%d)', $company->getName(), $company->getId()));
            $tableOrder = new Yourdelivery_Model_DbTable_Order();
            $count = $tableOrder->select('id')
                    ->where('companyId=?', $company->getId())
                    ->where('(billCompany=0 OR billCompany IS NULL)')
                    ->where('state > 0')
                    ->query()
                    ->rowCount();
            $tableAsset = new Yourdelivery_Model_DbTable_BillingAsset();
            $count += $tableAsset->select('id')
                    ->where('companyId=?', $company->getId())
                    ->where('(billCompany=0 OR billCompany IS NULL)')
                    ->query()
                    ->rowCount();
            clog('debug', sprintf('found %d orders to generate', $count));
            for ($i = 0; $i < $count; $i++) {
                clog('debug', sprintf('creating bill for transaction %d', $i));
                $bill = $company->getNextBill();
                $bill->create() ? $countCompany[0]++ : $countCompany[1]++;
                $bill->cleanup();
                unset($bill);
            }
            echo "C";
            break;

        case Yourdelivery_Model_Billing::BILL_PER_TWO_WEEKS:
            if ((integer) date('d') == 1 || (integer) date('d') == 16) {
                $create = true;
            }
            break;

        case Yourdelivery_Model_Billing::BILL_PER_DAY:
            $create = true;
            break;

        case Yourdelivery_Model_Billing::BILL_PER_MONTH:
            if ((integer) date('d') == 1) {
                $create = true;
            }
            break;
    }

    //create bill
    if ($create === true) {
        $bill = $company->getNextBill();
        $bill->create() ? $countCompany[0]++ : $countCompany[1]++;
        $bill->cleanUp();
        unset($bill, $company);
        echo "C";
    } else {
        echo "X";
    }
}

echo "\n\n";

/**
 * create bill for restaurants
 */
foreach (Yourdelivery_Model_Servicetype_Abstract::all(true) as $restaurant) {

    $create = false;
    switch ($restaurant->getBillInterval()) {

        case Yourdelivery_Model_Billing::BILL_PER_TRANSACTION:
            $create = true;
            break;

        case Yourdelivery_Model_Billing::BILL_PER_TWO_WEEKS:
            if ((integer) date('d') == 1 || (integer) date('d') == 16) {
                $create = true;
            }
            break;

        case Yourdelivery_Model_Billing::BILL_PER_DAY:
            $create = true;
            break;

        case Yourdelivery_Model_Billing::BILL_PER_MONTH:
            if ((integer) date('d') == 1) {
                $create = true;
            }
            break;
    }

    if ($create === true) {
        $bill = $restaurant->getNextBill();
        $bill->create() ? $countRestaurant[0]++ : $countRestaurant[1]++;
        $bill->cleanUp();
        $restaurant->cleanUp();
        unset($bill, $restaurant);
        echo "R";
    } else {
        echo "X";
    }
}

echo "\n\n";

/**
 * create bill for courier
 */
foreach (Yourdelivery_Model_Courier::all() as $courier) {

    $create = false;
    switch ($courier->getBillInterval()) {

        case Yourdelivery_Model_Billing::BILL_PER_TRANSACTION:
            $create = true;
            break;

        case Yourdelivery_Model_Billing::BILL_PER_TWO_WEEKS:
            if ((integer) date('d') == 1 || (integer) date('d') == 16) {
                $create = true;
            }
            break;

        case Yourdelivery_Model_Billing::BILL_PER_DAY:
            $create = true;
            break;

        case Yourdelivery_Model_Billing::BILL_PER_MONTH:
            if ((integer) date('d') == 1) {
                $create = true;
            }
            break;
    }

    if ($create === true) {
        $bill = $courier->getNextBill();
        $bill->create() ? $countCourier[0]++ : $countCourier[1]++;
        $bill->cleanUp();
        $courier->cleanUp();
        unset($bill, $courier);
    } else {
        echo "X";
    }
}

//final report
echo "Firmenrechnungen: " . implode('/', $countCompany) . "\n";
echo "Dienstleisterrechnungen: " . implode('/', $countRestaurant) . "\n";
echo "Kurierrechnungen: " . implode('/', $countCourier) . "\n";