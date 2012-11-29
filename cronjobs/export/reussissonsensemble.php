<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * YD-1306
 * @author Alex Vait <vait@lieferando.de>
 * @since 08.03.2012
 */

clog('info', 'creating reussissonsensemble statistics');

$db = Zend_Registry::get('dbAdapterReadOnly');
$data = $db->fetchAll(
    "SELECT
    CAST(CONCAT('\'FR_code_postal_', c.id, '\'') as char) as ArtNumber
    ,'\'Code Postal\'' as Category
    ,CONCAT('\'Commander à ', c.plz, '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/taxiresto.fr-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, '\'') as DescriptionShort
    ,CAST(CONCAT('\'', COALESCE(restPerPlz.restaurantIds, ''), '\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.taxiresto.fr/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
FROM `taxiresto.fr`.city c
LEFT JOIN `taxiresto.fr`.data_view_affiliate_feed_restaurantid_per_plz restPerPlz ON c.plz = restPerPlz.plz
WHERE c.parentCityId = 0
    AND c.id not IN (14464,14465,14466,14467,14468,14469,14470,14471,14472,14473,14474,14475,14476)#FR: PLZs 00000
GROUP BY c.plz
####################################
UNION ALL
SELECT
    CAST(CONCAT('\'FR_zone_de_livraison_', c.id, '\'') as char) as ArtNumber
    ,'\'Zone de livraison\'' as Category
    ,CONCAT('\'Commander à ', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as Title
    ,'\'http://image.yourdelivery.de/logo/taxiresto.fr-155-100.jpg\'' as ImgUrl
    ,CONCAT('\'', c.plz, ' ', if(c2.id IS NULL, '', CONCAT(c2.city, ' ')), if(c2.id IS NULL, c.city, CONCAT('(',c.city,')')), '\'') as DescriptionShort
    ,CONCAT('\'', COALESCE(lieferSub.AnzahlRestonline, ''), '\'') as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.taxiresto.fr/', c.restUrl, '\'') as Deeplink1
    ,'\'\'' as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,'\'\'' as ShippingPrefix
    ,CAST('\'0.00\'' as CHAR) as Shipping
    ,'\'EUR\'' as ShippingSuffix
FROM `taxiresto.fr`.city c
LEFT JOIN `taxiresto.fr`.city c2 ON c2.id = c.parentCityId AND c.parentCityId > 0
LEFT JOIN `taxiresto.fr`.data_view_affiliate_feed_restaurantid_per_city lieferSub ON lieferSub.cid = c.id
WHERE 1
    AND c.id not IN (14464,14465,14466,14467,14468,14469,14470,14471,14472,14473,14474,14475,14476)#FR: PLZs 00000
####################################
UNION ALL
SELECT
    CAST(CONCAT('\'FR_restaurant_', r.id, '\'') as char) as ArtNumber
    ,'\'Restaurant\'' as Category
    ,CONCAT('\'Commander chez ', r.name, '\'') as Title
    ,CAST(CONCAT('\'http://image.yourdelivery.de/taxiresto.fr/service/', r.id ,'/aff-155-100.jpg\'') as char) as ImgUrl
    ,CONCAT('\'', r.name, '\'') as DescriptionShort
    ,CAST(COALESCE(CONCAT('\'', FORMAT(CEIL((SUM(rr.quality)+SUM(rr.delivery))/(COUNT(rr.quality)+COUNT(rr.delivery))/.5)*.5 , 1) ,' étoiles (', COUNT(rr.id), ' évaluations)','\''), '\'\'') AS CHAR) as Description
    ,'\'0.00\'' as Price
    ,CONCAT('\'http://www.taxiresto.fr/', r.restUrl, '\'') as Deeplink1
    ,IF(r.franchiseTypeId = 5, '\'Bloomsburys\'', '\'\'') as Distributor
    ,'\'100\'' as ImgHeight
    ,'\'155\'' as ImgWidth
    ,IF(MIN(rp.delcost) != Max(rp.delcost), '\'à partir de\'', '\'\'') as ShippingPrefix
    ,CONCAT('\'', CAST(CAST(MIN(rp.delcost)/100 as DECIMAL(5,2)) AS CHAR), '\'') as Shipping
    ,'\'EUR\'' as ShippingSuffix
FROM `taxiresto.fr`.restaurants r
LEFT JOIN `taxiresto.fr`.restaurant_plz rp ON rp.restaurantId = r.id
LEFT JOIN `taxiresto.fr`.restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status = 1
WHERE 1
    AND r.deleted = 0
    AND r.isOnline = 1
    AND r.id NOT IN (664)#FR
GROUP BY r.id;");

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
"ShippingSuffix" . "\t\n";


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
        $d['ShippingSuffix'] . "\t\n";
}

$tmpfile = 'tmp/reussissonsensemble.txt';

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

        if (!@ftp_chdir($conn, "reussissonsensemble")) {
            ftp_mkdir($conn, "reussissonsensemble");
            ftp_chdir($conn, "reussissonsensemble");
        }

        $res = ftp_put($conn, 'taxiresto.fr.txt', $tmpfile, FTP_BINARY);

        if (!$res) {
            clog('err', 'failed to upload taxiresto.fr.txt file for reussissonsensemble');
        }
        ftp_close($conn);
    } 
    else {
        clog('err', 'cannot login to the ftp server to upload taxiresto.fr.txt file for reussissonsensemble');
    }
} 
else {
    clog('err', 'cannot connect to the ftp server to upload taxiresto.fr.txt file for reussissonsensemble');
}