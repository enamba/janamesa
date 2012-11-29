<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * YD-1163
 * @author Alex Vait <vait@lieferando.de>
 * @since 08.03.2012
 */

clog('info', 'creating 12 monkeys statistics');

$db = Zend_Registry::get('dbAdapterReadOnly');

$db->query("SET SESSION group_concat_max_len = 100000;");

$data = $db->fetchAll(
    "SELECT
    r.id as 'product_id'
    ,r.name as 'product_name'
    ,0.00 as 'product_price'
    ,CAST(COALESCE(CONCAT(   FORMAT( round((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' Sterne (', COUNT(DISTINCT(rr.id)), ' Meinungen)'),'') AS CHAR) as 'product_description'
    ,CONCAT('http://www.lieferando.de/', r.resturl, '?utm_source=ret&utm_medium=dp&utm_campaign=dp_14&utm_content=dp_13') as 'product_deeplink'
    ,CAST(CONCAT('http://image.yourdelivery.de/lieferando.de/service/', r.id, '/12mks-155-100.jpg') as char) as 'product_image_url'
    ,CAST(GROUP_CONCAT(DISTINCT(REPLACE(CONCAT(c.plz,'-',IF(c2.city IS NULL,c.city, CONCAT(c2.city,'-(',c.city,')'))),' ','-')) SEPARATOR ',') AS CHAR) as 'liefergebiete'
FROM restaurants r
LEFT JOIN data_view_affiliate_feed_restaurantid_per_city_sub rpc ON rpc.restaurantId = r.id
JOIN city c ON c.id = rpc.cid AND c.plz NOT IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
LEFT JOIN city c2 ON c2.id = c.parentCityId AND c2.plz NOT IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
LEFT JOIN restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status != 0
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (17697,16522,16521,13765,13607)#DE
GROUP by r.id;");


$csv = new Default_Exporter_Csv();
$csv->addCol('product_id');
$csv->addCol('product_name');
$csv->addCol('product_price');
$csv->addCol('product_description');
$csv->addCol('product_deeplink');
$csv->addCol('product_image_url');
$csv->addCol('liefergebiete');


foreach ($data as $d) {
    $csv->addRow(
            array(
                'product_id' => $d['product_id'],
                'product_name' => $d['product_name'],
                'product_price' => $d['product_price'],
                'product_description' => $d['product_description'],
                'product_deeplink' => $d['product_deeplink'],
                'product_image_url' => $d['product_image_url'],
                'liefergebiete' => $d['liefergebiete']
            )
    );
}

$file = $csv->save();

$conn = ftp_connect('IP');

if ($conn) {
    if (ftp_login($conn, 'USER', 'PASS')) {
        ftp_pasv($conn, true);

        if (!@ftp_chdir($conn, "export")) {
            ftp_mkdir($conn, "export");
            ftp_chdir($conn, "export");
        }

        if (!@ftp_chdir($conn, "csv")) {
            ftp_mkdir($conn, "csv");
            ftp_chdir($conn, "csv");
        }

        if (!@ftp_chdir($conn, "12monkeys")) {
            ftp_mkdir($conn, "12monkeys");
            ftp_chdir($conn, "12monkeys");
        }

        $res = ftp_put($conn, '12monkeys.csv', $file, FTP_BINARY);

        if (!$res) {
            clog('err', 'failed to upload 12monkeys.csv');
        }
        ftp_close($conn);
    } 
    else {
        clog('err', 'cannot login to the ftp server to upload 12monkeys.csv');
    }
} 
else {
    clog('err', 'cannot connect to the ftp server to upload 12monkeys.csv');
}