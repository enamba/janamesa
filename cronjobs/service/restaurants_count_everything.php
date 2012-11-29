<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all online restaurants with direct links and tags
 * mail must be send every monday to Jenny
 * @author alex
 * @since 17.05.2011
 */
clog('info', 'sending csv with restaurants data - counting everything for this restaurant');

$csv = new Default_Exporter_Csv();
$csv->addCol('RestaurantId');
$csv->addCol('Restaurant');
$csv->addCol('Online');
$csv->addCol('Erstellt');
$csv->addCol('Anzahl Liefergebiete');
$csv->addCol('Anzahl Öffnungszeiten');
$csv->addCol('Anzahl Speisen');
$csv->addCol('Anzahl Speisekategorien');
$csv->addCol('Anzahl Extras');
$csv->addCol('Anzahl Optionen');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll("select r.id, r.name, r.created, r.isOnline from restaurants r where r.deleted=0");

foreach ($data as $d) {

    $plzdata = $db->fetchRow("select count(id) cnt from restaurant_plz where restaurantId=? and status=1", $d['id']);
    $opdata = $db->fetchRow("select count(id) cnt from restaurant_openings where restaurantId=?", $d['id']);
    $mdata = $db->fetchRow("select count(id) cnt from meals where restaurantId=? and deleted=0", $d['id']);
    $mcdata = $db->fetchRow("select count(id) cnt from meal_categories where restaurantId=?", $d['id']);
    $medata = $db->fetchRow("select count(me.id) cnt from meal_extras me join meal_extras_groups meg on meg.id=me.groupId where meg.restaurantId=?", $d['id']);
    $modata = $db->fetchRow("select count(mo.id) cnt from meal_options mo join meal_options_nn mon on mon.optionId=mo.id join meal_options_rows mor on mor.id=mon.optionRowId where mor.restaurantId=?", $d['id']);

    $csv->addRow(
            array(
                'RestaurantId' => $d['id'],
                'Restaurant' => $d['name'],
                'Online' => $d['isOnline'],
                'Erstellt' => $d['created'],
                'Anzahl Liefergebiete' => $plzdata['cnt'],
                'Anzahl Öffnungszeiten' => $opdata['cnt'],
                'Anzahl Speisen' => $mdata['cnt'],
                'Anzahl Speisekategorien' => $mcdata['cnt'],
                'Anzahl Extras' => $medata['cnt'],
                'Anzahl Optionen' => $modata['cnt']
            )
    );
}

$file = $csv->save();

$conn = ftp_connect('46.163.72.129');
if ($conn) {
    // ftp login
    if (ftp_login($conn, 'yopeso', 'bcXVEvBPQmrSPPB6')) {
        ftp_pasv($conn, true);

        $config = Zend_Registry::get('configuration');

        $res = ftp_put($conn, 'Daten_ueber_alle_Restaurants_' . $config->domain->base . '_' . date("Ymd") . '.csv', $file, FTP_BINARY);
        if (!$res) {
            clog('err', 'failed to upload data for yopeso');
        }
        ftp_close($conn);
    } else {
        clog('err', 'cannot login to the ftp server to upload data for yopeso');
    }
} else {
    clog('err', 'cannot connect to the ftp server to upload data for yopeso');
}

