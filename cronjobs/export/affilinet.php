<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * YD-1294
 * @author Alex Vait <vait@lieferando.de>
 * @since 07.03.2012
 */

clog('info', 'creating affilinet statistics');

$db = Zend_Registry::get('dbAdapterReadOnly');
$db->query("SET SESSION group_concat_max_len = 100000;");

$data = $db->fetchAll(
        "(
SELECT
    CAST(CONCAT('\'DE_postleitzahl_', c.id, '\'') as char) as ArtNumber
    ,'\'Postleitzahl\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.de-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, '\'') as DescriptionShort
    ,CAST(CONCAT('\'', COALESCE(restPerPlz.restaurantIds, ''), '\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.de/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.de`.city c
LEFT JOIN `lieferando.de`.data_view_affiliate_feed_restaurantid_per_plz restPerPlz ON c.plz = restPerPlz.plz
WHERE c.parentCityId = 0
    AND c.plz not IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
GROUP BY c.plz
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'DE_liefergebiet_', c.id, '\'') as char) as ArtNumber
    ,'\'Liefergebiet\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.de-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as DescriptionShort
    ,CONCAT('\'', COALESCE(lieferSub.AnzahlRestonline, ''), '\'') as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.de/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.de`.city c
LEFT JOIN `lieferando.de`.city c2 ON c2.id = c.parentCityId AND c.parentCityId > 0
LEFT JOIN `lieferando.de`.data_view_affiliate_feed_restaurantid_per_city lieferSub ON lieferSub.cid = c.id
WHERE 1
    AND c.plz not IN (01070,01071,01072,01073,01074,01075,01076,01077,01078,01079,01080,01081,01082,01083,01084,01085,01086,01087,01088,01089)#DE
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'DE_restaurant_', r.id, '\'') as char) as ArtNumber
    ,'\'Restaurant\'' as Category
    ,CONCAT('\'Essen bestellen bei ', r.name, '\'') as Title
    ,CAST(CONCAT('\'http://image.yourdelivery.de/lieferando.de/service/', r.id ,'/aff-155-100.jpg\'') as char) as ImgUrl
    ,CONCAT('\'', r.name, '\'') as DescriptionShort
    ,CAST(COALESCE(CONCAT('\'', FORMAT(CEIL((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' Sterne (', COUNT(DISTINCT(rr.id)), ' Meinungen)','\''), '\'\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.de/', r.restUrl, '\'') as Deeplink1
    ,IF(r.franchiseTypeId = 5, '\'Bloomsburys\'', '\'\'') as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,IF(MIN(rp.delcost) != Max(rp.delcost), '\'ab\'', '\'\'') as ShippingPrefix
    ,CONCAT('\'', CAST(CAST(MIN(rp.delcost)/100 as DECIMAL(5,2)) AS CHAR), '\'') as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,CONCAT('\'', COALESCE(GROUP_CONCAT(DISTINCT(rt.tag)),''), '\'') as essensKategorien
    ,CONCAT('\'', r.street, ' ', r.hausNr, '|', r.plz, '|', COALESCE(cRestaurant.city, ''), '\'') as restaurantAdresse
    ,if(r.onlycash = 1, '\'bar\'', IF(r.paymentbar = 1, '\'bar, online\'' , '\'online\'')) as restaurantBezahloptionen
    ,CONCAT('\'', COALESCE(openingsSub.openingsForCurrentCalendarWeek, '||||||'), '\'') as Oeffnungszeiten
FROM `lieferando.de`.restaurants r
LEFT JOIN `lieferando.de`.restaurant_plz rp ON rp.restaurantId = r.id
LEFT JOIN `lieferando.de`.restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status != 0
LEFT JOIN `lieferando.de`.restaurant_tags rt ON rt.restaurantId = r.id
LEFT JOIN `lieferando.de`.city cRestaurant ON cRestaurant.id = r.cityId
LEFT JOIN `lieferando.de`.data_view_affiliate_feed_restaurant_openings openingsSub ON openingsSub.restaurantId = r.id
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (17697,16522,16521,13765,13607)#DE
GROUP BY r.id
)
#####################################
#      AT:                          #
#####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'AT_postleitzahl_', c.id, '\'') as char) as ArtNumber
    ,'\'Postleitzahl\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.at-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, '\'') as DescriptionShort
    ,CAST(CONCAT('\'', COALESCE(restPerPlz.restaurantIds, ''), '\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.at/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.at`.city c
LEFT JOIN `lieferando.at`.data_view_affiliate_feed_restaurantid_per_plz restPerPlz ON c.plz = restPerPlz.plz
WHERE c.parentCityId = 0
    AND c.plz not IN (6666,01070)#AT
GROUP BY c.plz
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'AT_liefergebiet_', c.id, '\'') as char) as ArtNumber
    ,'\'Liefergebiet\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.at-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as DescriptionShort
    ,CONCAT('\'', COALESCE(lieferSub.AnzahlRestonline, ''), '\'') as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.at/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.at`.city c
LEFT JOIN `lieferando.at`.city c2 ON c2.id = c.parentCityId AND c.parentCityId > 0
LEFT JOIN `lieferando.at`.data_view_affiliate_feed_restaurantid_per_city lieferSub ON lieferSub.cid = c.id
WHERE 1
    AND c.plz not IN (6666,01070)#AT
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'AT_restaurant_', r.id, '\'') as char) as ArtNumber
    ,'\'Restaurant\'' as Category
    ,CONCAT('\'Essen bestellen bei ', r.name, '\'') as Title
    ,CAST(CONCAT('\'http://image.yourdelivery.de/lieferando.at/service/', r.id ,'/aff-155-100.jpg\'') as char) as ImgUrl
    ,CONCAT('\'', r.name, '\'') as DescriptionShort
    ,CAST(COALESCE(CONCAT('\'', FORMAT(CEIL((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' Sterne (', COUNT(DISTINCT(rr.id)), ' Meinungen)','\''), '\'\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.at/', r.restUrl, '\'') as Deeplink1
    ,IF(r.franchiseTypeId = 5, '\'Bloomsburys\'', '\'\'') as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,IF(MIN(rp.delcost) != Max(rp.delcost), '\'ab\'', '\'\'') as ShippingPrefix
    ,CONCAT('\'', CAST(CAST(MIN(rp.delcost)/100 as DECIMAL(5,2)) AS CHAR), '\'') as Shipping
    ,'\'EUR\'' as ShippingSuffix
    ,CONCAT('\'', COALESCE(GROUP_CONCAT(DISTINCT(rt.tag)),''), '\'') as essensKategorien
    ,CONCAT('\'', r.street, ' ', r.hausNr, '|', r.plz, '|', COALESCE(cRestaurant.city, ''), '\'') as restaurantAdresse
    ,if(r.onlycash = 1, '\'bar\'', IF(r.paymentbar = 1, '\'bar, online\'' , '\'online\'')) as restaurantBezahloptionen
    ,CONCAT('\'', COALESCE(openingsSub.openingsForCurrentCalendarWeek, '||||||'), '\'') as Oeffnungszeiten
FROM `lieferando.at`.restaurants r
LEFT JOIN `lieferando.at`.restaurant_plz rp ON rp.restaurantId = r.id
LEFT JOIN `lieferando.at`.restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status != 0
LEFT JOIN `lieferando.at`.restaurant_tags rt ON rt.restaurantId = r.id
LEFT JOIN `lieferando.at`.city cRestaurant ON cRestaurant.id = r.cityId
LEFT JOIN `lieferando.at`.data_view_affiliate_feed_restaurant_openings openingsSub ON openingsSub.restaurantId = r.id
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (235)#AT
GROUP BY r.id
)
#####################################
#      CH:                          #
#####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'CH_postleitzahl_', c.id, '\'') as char) as ArtNumber
    ,'\'Postleitzahl\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.ch-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, '\'') as DescriptionShort
    ,CAST(CONCAT('\'', COALESCE(restPerPlz.restaurantIds, ''), '\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.ch/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'CHF\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.ch`.city c
LEFT JOIN `lieferando.ch`.data_view_affiliate_feed_restaurantid_per_plz restPerPlz ON c.plz = restPerPlz.plz
WHERE c.parentCityId = 0
    #AND c.plz not IN ()#CH
GROUP BY c.plz
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'CH_liefergebiet_', c.id, '\'') as char) as ArtNumber
    ,'\'Liefergebiet\'' as Category
    ,CONCAT('\'Essen bestellen in ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/lieferando.ch-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as DescriptionShort
    ,CONCAT('\'', COALESCE(lieferSub.AnzahlRestonline, ''), '\'') as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.ch/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'CHF\'' as ShippingSuffix
    ,'\'\'' as essensKategorien
    ,'\'\'' as restaurantAdresse
    ,'\'\'' as restaurantBezahloptionen
    ,'\'\'' as Oeffnungszeiten
FROM `lieferando.ch`.city c
LEFT JOIN `lieferando.ch`.city c2 ON c2.id = c.parentCityId AND c.parentCityId > 0
LEFT JOIN `lieferando.ch`.data_view_affiliate_feed_restaurantid_per_city lieferSub ON lieferSub.cid = c.id
WHERE 1
    #AND c.plz not IN ()#CH
)
####################################
UNION ALL
(
SELECT
    CAST(CONCAT('\'CH_restaurant_', r.id, '\'') as char) as ArtNumber
    ,'\'Restaurant\'' as Category
    ,CONCAT('\'Essen bestellen bei ', r.name, '\'') as Title
    ,CAST(CONCAT('\'http://image.yourdelivery.de/lieferando.ch/service/', r.id ,'/aff-155-100.jpg\'') as char) as ImgUrl
    ,CONCAT('\'', r.name, '\'') as DescriptionShort
    ,CAST(COALESCE(CONCAT('\'', FORMAT(CEIL((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' Sterne (', COUNT(DISTINCT(rr.id)), ' Meinungen)','\''), '\'\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.lieferando.ch/', r.restUrl, '\'') as Deeplink1
    ,IF(r.franchiseTypeId = 5, '\'Bloomsburys\'', '\'\'') as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,IF(MIN(rp.delcost) != Max(rp.delcost), '\'ab\'', '\'\'') as ShippingPrefix
    ,CONCAT('\'', CAST(CAST(MIN(rp.delcost)/100 as DECIMAL(5,2)) AS CHAR), '\'') as Shipping
    ,'\'CHF\'' as ShippingSuffix
    ,CONCAT('\'', COALESCE(GROUP_CONCAT(DISTINCT(rt.tag)),''), '\'') as essensKategorien
    ,CONCAT('\'', r.street, ' ', r.hausNr, '|', r.plz, '|', COALESCE(cRestaurant.city, ''), '\'') as restaurantAdresse
    ,if(r.onlycash = 1, '\'bar\'', IF(r.paymentbar = 1, '\'bar, online\'' , '\'online\'')) as restaurantBezahloptionen
    ,CONCAT('\'', COALESCE(openingsSub.openingsForCurrentCalendarWeek, '||||||'), '\'') as Oeffnungszeiten
FROM `lieferando.ch`.restaurants r
LEFT JOIN `lieferando.ch`.restaurant_plz rp ON rp.restaurantId = r.id
LEFT JOIN `lieferando.ch`.restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status != 0
LEFT JOIN `lieferando.ch`.restaurant_tags rt ON rt.restaurantId = r.id
LEFT JOIN `lieferando.ch`.city cRestaurant ON cRestaurant.id = r.cityId
LEFT JOIN `lieferando.ch`.data_view_affiliate_feed_restaurant_openings openingsSub ON openingsSub.restaurantId = r.id
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (49)#CH
GROUP BY r.id
)"
    );

$result = 
"ArtNumber" . "\t" .
"Category" . "\t" .
"Title" . "\t" .
"ImgUrl" . "\t" .
"DescriptionShort" . "\t" .
"Description" . "\t" .
"Price" . "\t" .
"Deeplink1" . "\t" .
"Distributor" . "\t" .
"ImgHeight" . "\t" .
"ImgWidth" . "\t" .
"ShippingPrefix" . "\t" .
"Shipping" . "\t" .
"ShippingSuffix" . "\t".
"essensKategorien" ."\t" .
"restaurantAdresse" ."\t" .
"restaurantBezahloptionen" ."\t" .
"Oeffnungszeiten" ."\t\n";


foreach ($data as $d) {
    $result .= 
        $d['ArtNumber'] . "\t" .
        $d['Category'] . "\t" .
        $d['Title'] . "\t" .
        $d['ImgUrl'] . "\t" .
        $d['DescriptionShort'] . "\t" .
        $d['Description'] . "\t" .
        $d['Price'] . "\t" .
        $d['Deeplink1'] . "\t" .
        $d['Distributor'] . "\t" .
        $d['ImgHeight'] . "\t" .
        $d['ImgWidth'] . "\t" .
        $d['ShippingPrefix'] . "\t" .
        $d['Shipping'] . "\t" .
        $d['ShippingSuffix'] . "\t" . 
        $d['essensKategorien'] . "\t" . 
        $d['restaurantAdresse'] . "\t" . 
        $d['restaurantBezahloptionen'] . "\t" . 
        $d['Oeffnungszeiten'] . "\t\n";
}

$tmpfile = 'tmp/affilinet.txt';

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

        if (!@ftp_chdir($conn, "affilinet")) {
            ftp_mkdir($conn, "affilinet");
            ftp_chdir($conn, "affilinet");
        }

        $res = ftp_put($conn, 'lieferando_de_at_ch.txt', $tmpfile, FTP_BINARY);

        if (!$res) {
            clog('err', 'failed to upload lieferando_de_at_ch.txt file for affilinet');
        }
        ftp_close($conn);
    } 
    else {
        clog('err', 'cannot login to the ftp server to upload lieferando_de_at_ch.txt file for affilinet');
    }
} 
else {
    clog('err', 'cannot connect to the ftp server to upload lieferando_de_at_ch.txt file for affilinet');
}