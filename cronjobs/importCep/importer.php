<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$row = 1;
$ceps = array();
if (($handle = fopen("../../storage/csvimportCep/last.csv", "r")) !== FALSE) {
    while (($ceps[] = fgetcsv($handle, 1000, ";")) !== FALSE) { }
    fclose($handle);
}
array_pop($ceps);
//$ceps = array(); $ceps[] = array('Rio de Janeiro', 'RJ', '22451-210', 769);
$tem = 0;
$nao = 0;
$cost = array();
$erro = array();
$cpe = array();
$countAll = 0;
foreach ($ceps as $cep) {
    $countAll++;
    $city = new Yourdelivery_Model_DbTable_City();
    $cityArr = $city->findByPlz($cep['2']);
    
    echo $countAll . '/'. count($ceps). '-' ;
    if (count($cityArr) == 0){
        $values['city'] = $cep[0];
        $values['state'] = $cep[0];
        $values['stateId'] = 2;
        $values['plz'] = $cep[2];
        $values['restUrl'] = 'servico-de-entrega-rio-de-janeiro-'.$cep[2];
        $values['caterUrl'] = 'encomenda-rio-de-janeiro-'.$cep[2];
        $values['greatUrl']='supermercado-rio-de-janeiro-'.$cep[2];

        $city = new Yourdelivery_Model_City();
        $city->setData($values);
        $city->save();
        $city = new Yourdelivery_Model_DbTable_City();
        $cityArr = $city->findByPlz($cep['2']);
        echo  $cep['2'] . ': ';
    }
    
    for ($cnt = 3; $cnt < count($cep) && !empty($cep[$cnt]) ; $cnt++) {
        $plz = Yourdelivery_Model_DbTable_Restaurant_Plz::findByRestaurantId($cep[$cnt]);
        $alreadyAssigned = Yourdelivery_Model_DbTable_Restaurant_Plz::findByRestaurantIdAndCityId($cep[$cnt], $cityArr[0]['id']);
        if ($plz['deliverTime'] != '' && (is_null($alreadyAssigned) || empty($alreadyAssigned))) {
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($cep[$cnt]);
            $restaurant->createLocation($cityArr[0]['id'], intval($plz['mincost'])/100, intval($plz['delcost'])/100, $plz['deliverTime'], $plz['noDeliverCostAbove']);
            echo $cep[$cnt]. ';';
        }
    }
    echo "\n";
}


?>
