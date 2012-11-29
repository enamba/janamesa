<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * YD-1295
 * @author Alex Vait <vait@lieferando.de>
 * @since 08.03.2012
 */

clog('info', 'creating belboon statistics');

$db = Zend_Registry::get('dbAdapterReadOnly');
$data = $db->fetchAll(
    "SELECT
    CAST(CONCAT('\'DE_postleitzahl_', c.id, '\'') as char) as Merchant_ProductNumber
    ,'\'\'' as EAN_Code
    ,CONCAT('\'Essen bestellen in ', c.plz, '\'') as Product_Title
    ,'\'\'' as Manufacturer
    ,'\'\'' as Brand
    ,'\'0.00\'' as Price
    ,'\'0.00\'' as Price_old
    ,'\'EUR\'' as Currency
    ,'\'\'' as Valid_From
    ,'\'\'' as Valid_To
    ,CONCAT('\'http://www.lieferando.de/', c.restUrl, '\'') as DeepLink_URL
    ,'\'\'' as Into_Basket_URL
    ,CAST(CONCAT('\'http://image.yourdelivery.de/logo/lieferando.de-155-100.jpg\'') as char) as Image_Small_URL
    ,'\'100\'' as Image_Small_HEIGHT
    ,'\'155\'' as Image_Small_WIDTH
    ,'\'\'' as Image_Large_URL
    ,'\'\'' as Image_Large_HEIGHT
    ,'\'\'' as Image_Large_WIDTH
    ,'\'DE_Postleitzahl\'' as Merchant_Product_Category
    ,CAST(CONCAT('\'', COALESCE(restPerPlz.restaurantIds, ''), '\'') AS CHAR) as Keywords
    ,CONCAT('\'', c.plz, '\'') as Product_Description_Short
    ,CONCAT('\'Auflistung der Restaurants, die die Postleitzahl ', c.plz, ' beliefern\'') as Product_Description_Long
    ,CONCAT('\'', DATE_FORMAT(now(), '%d-%m-%Y'), '\'') as Last_Update
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'sofort lieferbar\'' as Availability
    ,'\'\'' as Optional_1
    ,'\'\'' as Optional_2
    ,'\'\'' as Optional_3
    ,'\'\'' as Optional_4
    ,'\'\'' as Optional_5
FROM `lieferando.de`.city c
LEFT JOIN `lieferando.de`.data_view_affiliate_feed_restaurantid_per_plz restPerPlz ON c.plz = restPerPlz.plz
WHERE c.parentCityId = 0
    AND c.plz not IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
GROUP BY c.plz
####################################
UNION ALL
SELECT
    CAST(CONCAT('\'DE_liefergebiet_', c.id, '\'') as char) as Merchant_ProductNumber
    ,'\'\'' as EAN_Code
    ,CONCAT('\'Essen bestellen in ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Product_Title
    ,'\'\'' as Manufacturer
    ,'\'\'' as Brand
    ,'\'0.00\'' as Price
    ,'\'0.00\'' as Price_old
    ,'\'EUR\'' as Currency
    ,'\'\'' as Valid_From
    ,'\'\'' as Valid_To
    ,CONCAT('\'http://www.lieferando.de/', c.restUrl, '\'') as DeepLink_URL
    ,'\'\'' as Into_Basket_URL
    ,CAST(CONCAT('\'http://image.yourdelivery.de/logo/lieferando.de-155-100.jpg\'') as char) as Image_Small_URL
    ,'\'100\'' as Image_Small_HEIGHT
    ,'\'155\'' as Image_Small_WIDTH
    ,'\'\'' as Image_Large_URL
    ,'\'\'' as Image_Large_HEIGHT
    ,'\'\'' as Image_Large_WIDTH
    ,'\'DE_Liefergebiet\'' as Merchant_Product_Category
    ,CONCAT('\'', COALESCE(lieferSub.AnzahlRestonline, ''), '\'') as Keywords
    ,CONCAT('\'', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Product_Description_Short
    ,CONCAT('\'Auflistung der Restaurants, die das Liefergebiet ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), ' beliefern\'') as Product_Description_Long
    ,CONCAT('\'', DATE_FORMAT(now(), '%d-%m-%Y'), '\'') as Last_Update
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'sofort lieferbar\'' as Availability
    ,'\'\'' as Optional_1
    ,'\'\'' as Optional_2
    ,'\'\'' as Optional_3
    ,'\'\'' as Optional_4
    ,'\'\'' as Optional_5
FROM `lieferando.de`.city c
LEFT JOIN `lieferando.de`.city c2 ON c2.id = c.parentCityId AND c.parentCityId > 0
LEFT JOIN `lieferando.de`.data_view_affiliate_feed_restaurantid_per_city lieferSub ON lieferSub.cid = c.id
WHERE 1
    AND c.plz not IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
####################################
UNION ALL
SELECT
    CAST(CONCAT('\'DE_restaurant_', r.id, '\'') as char) as Merchant_ProductNumber
    ,'\'\'' as EAN_Code
    ,CONCAT('\'Essen bestellen bei ', r.name, '\'') as Product_Title
    ,CONCAT('\'', r.name, '\'') as Manufacturer
    ,'\'\'' as Brand
    ,'\'0.00\'' as Price
    ,'\'0.00\'' as Price_old
    ,'\'EUR\'' as Currency
    ,'\'\'' as Valid_From
    ,'\'\'' as Valid_To
    ,CONCAT('\'http://www.lieferando.de/', r.restUrl, '\'') as DeepLink_URL
    ,'\'\'' as Into_Basket_URL
    ,CAST(CONCAT('\'http://image.yourdelivery.de/lieferando.de/service/', r.id ,'/bel-155-100.jpg\'') as char) as Image_Small_URL
    ,'\'100\'' as Image_Small_HEIGHT
    ,'\'155\'' as Image_Small_WIDTH
    ,'\'\'' as Image_Large_URL
    ,'\'\'' as Image_Large_HEIGHT
    ,'\'\'' as Image_Large_WIDTH
    ,'\'DE_Postleitzahl\'' as Merchant_Product_Category
    ,'\'\'' as Keywords
    ,CONCAT('\'', r.name, '\'') as Product_Description_Short
    ,CAST(COALESCE(CONCAT('\'', FORMAT(CEIL((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' Sterne (', COUNT(rr.id), ' Meinungen)','\''), '\'\'') AS CHAR) as Product_Description_Long
    ,CONCAT('\'', DATE_FORMAT(now(), '%d-%m-%Y'), '\'') as Last_Update
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'sofort lieferbar\'' as Availability
    ,'\'\'' as Optional_1
    ,'\'\'' as Optional_2
    ,'\'\'' as Optional_3
    ,'\'\'' as Optional_4
    ,'\'\'' as Optional_5
FROM `lieferando.de`.restaurants r
LEFT JOIN `lieferando.de`.restaurant_plz rp ON rp.restaurantId = r.id
LEFT JOIN `lieferando.de`.restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status != 0
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (17697,16522,16521,13765,13607)
GROUP BY r.id;");

$result = 
"Merchant_ProductNumber" . "\t" .
"EAN_Code" . "\t" .
"Product_Title" . "\t" .
"Manufacturer" . "\t" .
"Brand" . "\t" .
"Price" . "\t" .
"Price_old" . "\t" .
"Currency" . "\t" .
"Valid_From" . "\t" .
"Valid_To" . "\t" .
"DeepLink_URL" . "\t" .
"Into_Basket_URL" . "\t" .
"Image_Small_URL" . "\t" .
"Image_Small_HEIGHT" . "\t" .
"Image_Small_WIDTH" . "\t" .
"Image_Large_URL" . "\t" .
"Image_Large_HEIGHT" . "\t" .
"Image_Large_WIDTH" . "\t" .
"Merchant_Product_Category" . "\t" .
"Keywords" . "\t" .
"Product_Description_Short" . "\t" .
"Product_Description_Long" . "\t" .
"Last_Update" . "\t" .
"Shipping" . "\t" .
"Availability" . "\t" .
"Optional_1" . "\t" .
"Optional_2" . "\t" .
"Optional_3" . "\t" .
"Optional_4" . "\t" .
"Optional_5" . "\t\n";

foreach ($data as $d) {
    $result .= 
        $d['Merchant_ProductNumber'] . "\t" .
        $d['EAN_Code'] . "\t" .
        $d['Product_Title'] . "\t" .
        $d['Manufacturer'] . "\t" .
        $d['Brand'] . "\t" .
        $d['Price'] . "\t" .
        $d['Price_old'] . "\t" .
        $d['Currency'] . "\t" .
        $d['Valid_From'] . "\t" .
        $d['Valid_To'] . "\t" .
        $d['DeepLink_URL'] . "\t" .
        $d['Into_Basket_URL'] . "\t" .
        $d['Image_Small_URL'] . "\t" .
        $d['Image_Small_HEIGHT'] . "\t" .
        $d['Image_Small_WIDTH'] . "\t" .
        $d['Image_Large_URL'] . "\t" .
        $d['Image_Large_HEIGHT'] . "\t" .
        $d['Image_Large_WIDTH'] . "\t" .
        $d['Merchant_Product_Category'] . "\t" .
        $d['Keywords'] . "\t" .
        $d['Product_Description_Short'] . "\t" .
        $d['Product_Description_Long'] . "\t" .
        $d['Last_Update'] . "\t" .
        $d['Shipping'] . "\t" .
        $d['Availability'] . "\t" .
        $d['Optional_1'] . "\t" .
        $d['Optional_2'] . "\t" .
        $d['Optional_3'] . "\t" .
        $d['Optional_4'] . "\t" .
        $d['Optional_5'] . "\t\n";
}

$tmpfile = '/tmp/belboon.txt';

$fh = fopen($tmpfile, 'w') or die("can't open file");
fwrite($fh, $result);
fclose($fh);

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

        if (!@ftp_chdir($conn, "belboon")) {
            ftp_mkdir($conn, "belboon");
            ftp_chdir($conn, "belboon");
        }

        $res = ftp_put($conn, 'lieferando_de.txt', $tmpfile, FTP_BINARY);

        if (!$res) {
            clog('err', 'failed to upload lieferando_de.txt file for belboon');
        }
        ftp_close($conn);
    } 
    else {
        clog('err', 'cannot login to the ftp server to upload lieferando_de.txt file for belboon');
    }
} 
else {
    clog('err', 'cannot connect to the ftp server to upload lieferando_de.txt file for belboon');
}