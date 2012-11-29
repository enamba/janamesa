<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all online restaurants with direct links and tags
 * mail must be send every monday to Jenny
 * @author alex
 * @since 17.05.2011
 */
clog('info', 'sending csv with online restaurants for seo');

$isAT = (Zend_Registry::get('configuration')->domain->base == "lieferando.at") ? true : false;

$csv = new Default_Exporter_Csv(false);
$csv->addCol('RestaurantId');
$csv->addCol('Restaurant');
$csv->addCol('IstOnline');
$csv->addCol('IstGeloescht');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('PLZ');
$csv->addCol('Stadt');

//hack für at Bezirke
if ($isAT) {
    $csv->addCol('Bezirk');
}
$csv->addCol('Direct Link');
$csv->addCol('AdSchedule');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "SELECT
    r.id as RestaurantId
    ,r.name as Restaurant
    ,r.isOnline as IstOnline
    ,r.deleted as IstGeloescht
    ,r.street as Strasse
    ,r.hausnr as Hausnr
    ,r.plz as PLZ
    ,IF(c2.city IS NOT NULL, c2.city,c.city) as Stadt
    ,COALESCE(r.restUrl,'') as DirektLink
    #Montag:
    ,CONCAT(CASE rosMonday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Monday@100%[',TIME_FORMAT(rosMonday.from, '%H:%i'),'-',TIME_FORMAT(rosMonday.until, '%H:%i'),'])' )) ORDER BY rosMonday.from ASC SEPARATOR ';')
        ELSE
            IF(rohMonday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Monday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roMonday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Monday@100%[',TIME_FORMAT(roMonday.from, '%H:%i'),'-',TIME_FORMAT(roMonday.until, '%H:%i'),'])' )) ORDER BY roMonday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Montag
    #Dienstag:
    CASE rosTuesday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Tuesday@100%[',TIME_FORMAT(rosTuesday.from, '%H:%i'),'-',TIME_FORMAT(rosTuesday.until, '%H:%i'),'])' )) ORDER BY rosTuesday.from ASC SEPARATOR ';')
        ELSE
            IF(rohTuesday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Tuesday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roTuesday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Tuesday@100%[',TIME_FORMAT(roTuesday.from, '%H:%i'),'-',TIME_FORMAT(roTuesday.until, '%H:%i'),'])' )) ORDER BY roTuesday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Dienstag
    #Mittwoch:
    CASE rosWednesday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Wednesday@100%[',TIME_FORMAT(rosWednesday.from, '%H:%i'),'-',TIME_FORMAT(rosWednesday.until, '%H:%i'),'])' )) ORDER BY rosWednesday.from ASC SEPARATOR ';')
        ELSE
            IF(rohWednesday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Wednesday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roWednesday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Wednesday@100%[',TIME_FORMAT(roWednesday.from, '%H:%i'),'-',TIME_FORMAT(roWednesday.until, '%H:%i'),'])' )) ORDER BY roWednesday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Mittwoch
    #Donnerstag:
    CASE rosThursday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Thursday@100%[',TIME_FORMAT(rosThursday.from, '%H:%i'),'-',TIME_FORMAT(rosThursday.until, '%H:%i'),'])' )) ORDER BY rosThursday.from ASC SEPARATOR ';')
        ELSE
            IF(rohThursday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Thursday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roThursday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Thursday@100%[',TIME_FORMAT(roThursday.from, '%H:%i'),'-',TIME_FORMAT(roThursday.until, '%H:%i'),'])' )) ORDER BY roThursday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Donnerstag
    #Freitag:
    CASE rosFriday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Friday@100%[',TIME_FORMAT(rosFriday.from, '%H:%i'),'-',TIME_FORMAT(rosFriday.until, '%H:%i'),'])' )) ORDER BY rosFriday.from ASC SEPARATOR ';')
        ELSE
            IF(rohFriday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Friday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roFriday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Friday@100%[',TIME_FORMAT(roFriday.from, '%H:%i'),'-',TIME_FORMAT(roFriday.until, '%H:%i'),'])' )) ORDER BY roFriday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Freitag
    #Sonnabend:
    CASE rosSaturday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Saturday@100%[',TIME_FORMAT(rosSaturday.from, '%H:%i'),'-',TIME_FORMAT(rosSaturday.until, '%H:%i'),'])' )) ORDER BY rosSaturday.from ASC SEPARATOR ';')
        ELSE
            IF(rohSaturday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Saturday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roSaturday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Saturday@100%[',TIME_FORMAT(roSaturday.from, '%H:%i'),'-',TIME_FORMAT(roSaturday.until, '%H:%i'),'])' )) ORDER BY roSaturday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END ,';',#as Sonnabend
    #Sonntag:
    CASE rosSunday.closed
        WHEN 1 THEN
           ''
        WHEN 0 THEN
            GROUP_CONCAT(DISTINCT(CONCAT('(Sunday@100%[',TIME_FORMAT(rosSunday.from, '%H:%i'),'-',TIME_FORMAT(rosSunday.until, '%H:%i'),'])' )) ORDER BY rosSunday.from ASC SEPARATOR ';')
        ELSE
            IF(rohSunday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
                ,GROUP_CONCAT(DISTINCT(CONCAT('(Sunday@100%[',TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'),'])' )) ORDER BY roFeiertag.from ASC SEPARATOR ';')
                ,IF(roSunday.id IS NOT NULL
                    ,GROUP_CONCAT(DISTINCT(CONCAT('(Sunday@100%[',TIME_FORMAT(roSunday.from, '%H:%i'),'-',TIME_FORMAT(roSunday.until, '%H:%i'),'])' )) ORDER BY roSunday.from ASC SEPARATOR ';')
                    ,''
                )
            )
    END #as Sonntag
    ) as AdSchedule
FROM restaurants r
JOIN city c ON c.id = r.cityId
LEFT JOIN city c2 ON c2.id = c.parentCityId
#Feiertag:
LEFT JOIN restaurant_openings roFeiertag ON roFeiertag.restaurantId = r.id AND roFeiertag.day = 10
#Montag:
LEFT JOIN restaurant_openings_special rosMonday ON rosMonday.restaurantId = r.id AND YEAR(rosMonday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosMonday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosMonday.specialDate,'%w') = 1
LEFT JOIN restaurant_openings_holidays rohMonday ON rohMonday.stateId = c.stateId AND YEAR(rohMonday.date) = YEAR(now()) AND WEEKOFYEAR(rohMonday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohMonday.date,'%w') = 1
LEFT JOIN restaurant_openings roMonday ON roMonday.restaurantId = r.id AND roMonday.day = 1
#Dienstag:
LEFT JOIN restaurant_openings_special rosTuesday ON rosTuesday.restaurantId = r.id AND YEAR(rosTuesday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosTuesday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosTuesday.specialDate,'%w') = 2
LEFT JOIN restaurant_openings_holidays rohTuesday ON rohTuesday.stateId = c.stateId AND YEAR(rohTuesday.date) = YEAR(now()) AND WEEKOFYEAR(rohTuesday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohTuesday.date,'%w') = 2
LEFT JOIN restaurant_openings roTuesday ON roTuesday.restaurantId = r.id AND roTuesday.day = 2
#Mittwoch:
LEFT JOIN restaurant_openings_special rosWednesday ON rosWednesday.restaurantId = r.id AND YEAR(rosWednesday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosWednesday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosWednesday.specialDate,'%w') = 3
LEFT JOIN restaurant_openings_holidays rohWednesday ON rohWednesday.stateId = c.stateId AND YEAR(rohWednesday.date) = YEAR(now()) AND WEEKOFYEAR(rohWednesday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohWednesday.date,'%w') = 3
LEFT JOIN restaurant_openings roWednesday ON roWednesday.restaurantId = r.id AND roWednesday.day = 3
#Donnerstag:
LEFT JOIN restaurant_openings_special rosThursday ON rosThursday.restaurantId = r.id AND YEAR(rosThursday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosThursday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosThursday.specialDate,'%w') = 4
LEFT JOIN restaurant_openings_holidays rohThursday ON rohThursday.stateId = c.stateId AND YEAR(rohThursday.date) = YEAR(now()) AND WEEKOFYEAR(rohThursday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohThursday.date,'%w') = 4
LEFT JOIN restaurant_openings roThursday ON roThursday.restaurantId = r.id AND roThursday.day = 4
#Freitag:
LEFT JOIN restaurant_openings_special rosFriday ON rosFriday.restaurantId = r.id AND YEAR(rosFriday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosFriday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosFriday.specialDate,'%w') = 5
LEFT JOIN restaurant_openings_holidays rohFriday ON rohFriday.stateId = c.stateId AND YEAR(rohFriday.date) = YEAR(now()) AND WEEKOFYEAR(rohFriday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohFriday.date,'%w') = 5
LEFT JOIN restaurant_openings roFriday ON roFriday.restaurantId = r.id AND roFriday.day = 5
#Sonnabend:
LEFT JOIN restaurant_openings_special rosSaturday ON rosSaturday.restaurantId = r.id AND YEAR(rosSaturday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosSaturday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosSaturday.specialDate,'%w') = 6
LEFT JOIN restaurant_openings_holidays rohSaturday ON rohSaturday.stateId = c.stateId AND YEAR(rohSaturday.date) = YEAR(now()) AND WEEKOFYEAR(rohSaturday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohSaturday.date,'%w') = 6
LEFT JOIN restaurant_openings roSaturday ON roSaturday.restaurantId = r.id AND roSaturday.day = 6
#Sonntag:
LEFT JOIN restaurant_openings_special rosSunday ON rosSunday.restaurantId = r.id AND YEAR(rosSunday.specialDate) = YEAR(now()) AND WEEKOFYEAR(rosSunday.specialDate) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rosSunday.specialDate,'%w') = 0
LEFT JOIN restaurant_openings_holidays rohSunday ON rohSunday.stateId = c.stateId AND YEAR(rohSunday.date) = YEAR(now()) AND WEEKOFYEAR(rohSunday.date) = (WEEKOFYEAR(now())) AND DATE_FORMAT(rohSunday.date,'%w') = 0
LEFT JOIN restaurant_openings roSunday ON roSunday.restaurantId = r.id AND roSunday.day = 0

GROUP BY r.id");




foreach ($data as $d) {

    if ($isAT) {

        $stadt = $d['city'];
        $bezirk = "";

        $cities_at = array("Wien", "Graz", "Klagenfurt");
        foreach ($cities_at as $city) {
            if (strstr($d['Stadt'], $city)) {
                $stadt = $city;
                $bezirk = trim(substr($d['Stadt'], strlen($city) + 1));
            }
        }
        $row = array(
            'RestaurantId' => $d['RestaurantId'],
            'Restaurant' => $d['Restaurant'],
            'IstOnline' => $d['IstOnline'],
            'IstGeloescht' => $d['IstGeloescht'],
            'Strasse' => $d['Strasse'],
            'Hausnr' => $d['Hausnr'],
            'PLZ' => $d['PLZ'],
            'Stadt' => $stadt,
            'Bezirk' => $bezirk,
            'Direct Link' => $d['DirektLink'],
            'AdSchedule' => '"' . $d['AdSchedule'] . '"'
        );
    } else {
        $row = array(
            'RestaurantId' => $d['RestaurantId'],
            'Restaurant' => $d['Restaurant'],
            'IstOnline' => $d['IstOnline'],
            'IstGeloescht' => $d['IstGeloescht'],
            'Strasse' => $d['Strasse'],
            'Hausnr' => $d['Hausnr'],
            'PLZ' => $d['PLZ'],
            'Stadt' => $d['Stadt'],
            'Direct Link' => $d['DirektLink'],
            'AdSchedule' => '"' . $d['AdSchedule'] . '"'
        );
    }

    $csv->addRow(
            $row
    );
}

$file = $csv->save();

//upload file to the ftp server
if (IS_PRODUCTION) {
    $conn = ftp_connect('46.163.72.129');
    if ($conn) {
        // ftp login
        if (ftp_login($conn, 'twormedia', 'zGPaABMHBDesp4pC')) {
            ftp_pasv($conn, true);

            $config = Zend_Registry::get('configuration');
            $domain = explode('.', $config->domain->base);

            $res = ftp_put($conn, 'Restaurants_on_' . $domain[1] . '.csv', $file, FTP_BINARY);
            if (!$res) {
                clog('err', 'failed to upload results from cronjob "restaurants_online"');
            }
            ftp_close($conn);
        } else {
            clog('err', 'cannot login to the ftp server to upload results from cronjob "restaurants_online"');
        }
    } else {
        clog('err', 'cannot connect to the ftp server to upload results from cronjob "restaurants_online"');
    }
}