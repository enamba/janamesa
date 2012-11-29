<?php

/**
 * add all restaurants to gelbeseiten, that are not yet connected 
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));
ini_set('max_execution_time', 0);

//create all entries for gelbeseiten
echo "CREATING ASSOCIATIONS TO SERVICES\n";
$dbWrite = Zend_Registry::get('dbAdapter');
$insert = "insert into satellites (restaurantId,domain,url,title) 
            select id,'gelbeseiten.lieferando.de',CONCAT('/service/',md5(r.customerNr)),CONCAT('Essen Bestellen bei ',r.name) from restaurants r 
                where deleted=0 and r.id not in (select restaurantId from satellites where domain='gelbeseiten.lieferando.de');";
$dbWrite->query($insert);

$db = Zend_Registry::get('dbAdapterReadOnly');

echo "GETTING RATINGS DATA \n";
//export ratings
$ratingsData = "SELECT
	md5(r.customerNr) as restaurantIdentifier
	,rr_latest.created as datetime1
	,COALESCE(REPLACE(REPLACE(REPLACE(rr_latest.comment, '\r\n',''), '\n',''), '\t',''),'') as text1
	,COALESCE(rr_before_latest.datetime,'') as datetime2
	,COALESCE(REPLACE(REPLACE(REPLACE(rr_before_latest.text, '\r\n',''), '\n',''), '\t',''),'') as text2
	,COALESCE(rr_before_before_latest.datetime,'') as datetime3
	,COALESCE(REPLACE(REPLACE(REPLACE(rr_before_before_latest.text, '\r\n',''), '\n',''), '\t',''),'') as text3
	,CONCAT('http://gelbeseiten.lieferando.de/service/',md5(r.customerNr),'#ratings') as linkToPopupWithRatings
FROM
#########################################################
#	jüngste Meinung:
#########################################################
(
	SELECT
		dlo.restaurantId
		,COALESCE((
			SELECT
				id
			FROM restaurant_ratings li
			WHERE 1
				AND li.restaurantId = dlo.restaurantId
				AND li.status = 1
				AND LENGTH(li.comment) > 0
			ORDER BY
                 li.restaurantId
				,li.id DESC
			LIMIT 1,1
		),0) AS id
	FROM (
		SELECT
			DISTINCT restaurantId
		FROM restaurant_ratings
		WHERE 1
			AND status = 1
			AND LENGTH(comment) > 0
	) dlo
) latest
,restaurant_ratings rr_latest
#########################################################
#	zweitjüngste Meinung:
#########################################################
LEFT JOIN
(
	SELECT
		rr_before_latest.id as id
		,rr_before_latest.restaurantId as restaurantId
		,rr_before_latest.created as datetime
		,rr_before_latest.comment as text
	FROM
	(
		SELECT
			dlo.restaurantId
			,COALESCE((
				SELECT
					id
				FROM restaurant_ratings li
				WHERE 1
					AND li.restaurantId = dlo.restaurantId
					AND li.status = 1
					AND LENGTH(li.comment) > 0
				ORDER BY
					 li.restaurantId
					,li.id DESC
				LIMIT 2,1
			),0) AS id
		FROM (
			SELECT
				DISTINCT restaurantId
			FROM restaurant_ratings
			WHERE 1
				AND status = 1
				AND LENGTH(comment) > 0
		) dlo
	) second_latest
	,restaurant_ratings rr_before_latest
	WHERE 1
		AND rr_before_latest.restaurantId = second_latest.restaurantId
		AND rr_before_latest.id > second_latest.id
		AND rr_before_latest.status = 1
		AND length(rr_before_latest.comment) > 0
) rr_before_latest ON rr_before_latest.restaurantId = rr_latest.restaurantId AND rr_latest.id > rr_before_latest.id
#########################################################
#	drittjüngste Meinung:
#########################################################
LEFT JOIN
(
	SELECT
		rr_before_before_latest.id as id
		,rr_before_before_latest.restaurantId as restaurantId
		,rr_before_before_latest.created as datetime
		,rr_before_before_latest.comment as text
	FROM
	(
		SELECT
			dlo.restaurantId
			,COALESCE((
				SELECT
					id
				FROM restaurant_ratings li
				WHERE 1
					AND li.restaurantId = dlo.restaurantId
					AND li.status = 1
					AND LENGTH(li.comment) > 0
				ORDER BY
					 li.restaurantId
					,li.id DESC
				LIMIT 3,1
			),0) AS id
		FROM (
			SELECT
				DISTINCT restaurantId
			FROM restaurant_ratings
			WHERE 1
				AND status = 1
				AND LENGTH(comment) > 0
		) dlo
	) third_latest
	,restaurant_ratings rr_before_before_latest
	WHERE 1
		AND rr_before_before_latest.restaurantId = third_latest.restaurantId
		AND rr_before_before_latest.id > third_latest.id
		AND rr_before_before_latest.status = 1
		AND length(rr_before_before_latest.comment) > 0
) rr_before_before_latest ON rr_before_before_latest.restaurantId = rr_latest.restaurantId AND rr_before_latest.id > rr_before_before_latest.id
#########################################################
#########################################################
JOIN restaurants r ON r.id = rr_latest.restaurantId
WHERE 1
	AND rr_latest.restaurantId = latest.restaurantId
	AND rr_latest.id > latest.id
	AND rr_latest.status = 1
	AND length(rr_latest.comment) > 0
	AND r.deleted = 0
;";


$csvRating = new Default_Exporter_Csv();
$csvRating->seperator = "\t";
$ratingsResult = $db->fetchAll($ratingsData);
echo "CREATING RATINGS CSV \n";
if (count($ratingsResult) > 0) {
    $csvRating->addCols(array_keys($ratingsResult[0]));
    $csvRating->addRows($ratingsResult);
}
$csvRating->save();

echo "GETTING RESTAURANT DATA \n";
$restaurantData = "
SELECT
    CONCAT('http://gelbeseiten.lieferando.de/service/', md5(r.customerNr)) as restaurantUrl
    ,md5(r.customerNr) as restaurantIdentifier
    ,r.name as restaurantName
    ,r.street as restaurantStrasse
    ,r.hausnr as restaurantHausnummer
    ,'[NA]' as restaurantHausnummerZusatz
    ,r.plz as restaurantPlz
    ,IF(c2.city IS NOT NULL, c2.city, c.city) as restaurantOrt
    ,r.tel as restaurantRufnummer
    ,if(r.onlycash = 1, 'bar', IF(r.paymentbar = 1, 'bar, online' , 'online')) as restaurantBezahloptionen
    ,rplzSub.mindestbestellwert as mindestbestellwert
    ,rplzSub.liefergebiete as liefergebiete
    ,COALESCE(GROUP_CONCAT(DISTINCT(rt.tag) ORDER BY rt.tag), '') as kueche
    ,COALESCE(ROUND(SUM(rr.delivery+rr.quality) / COUNT((rr.id))/2, 2), '') as bewertungsdurchschnittGesamt
    ,COALESCE(ROUND(SUM(rr.delivery) / COUNT((rr.id)), 2), '') as bewertungsdurchschnittLieferservice
    ,COALESCE(ROUND(SUM(rr.Quality) / COUNT((rr.id)), 2), '') as bewertungsdurchschnittQualitaet
    ,COALESCE(openingsSub.openingsForCurrentCalendarWeek, '||||||') as restaurantOeffnungszeiten
FROM restaurants r
#JOIN satellites s ON s.restaurantId = r.id
JOIN city c on c.id = r.cityId
#JOIN restaurant_plz rplz ON rplz.restaurantId = r.id
JOIN(
	select
		rplz.restaurantId
		,CONCAT(
			IF(
				MIN(rplz.mincost) != MAX(rplz.mincost)
				,'Ab '
				, ''
			)
			,CAST(MIN(rplz.mincost)/100 AS DECIMAL(10,2)), ' Euro'
		) as mindestbestellwert
		,GROUP_CONCAT(DISTINCT(rplz.plz) ORDER BY rplz.plz) as liefergebiete
	FROM restaurant_plz rplz
	GROUP BY rplz.restaurantId
) rplzSub ON rplzSub.restaurantId = r.id
LEFT JOIN city c2 ON c2.id = c.parentCityId
LEFT JOIN restaurant_tags rt ON rt.restaurantId = r.id
LEFT JOIN restaurant_ratings rr ON rr.restaurantId = r.id AND rr.status = 1
LEFT JOIN data_view_affiliate_feed_restaurant_openings openingsSub ON openingsSub.restaurantId = r.id

WHERE r.deleted = 0
    AND r.isOnline = 1
GROUP BY r.id
;";


$csvData = new Default_Exporter_Csv();
$csvData->seperator = "\t";
$db->query('SET SESSION group_concat_max_len = 100000;');
$restaurantResult = $db->fetchAll($restaurantData);
echo "CREATING RESTAURANT DATA CSV \n";
if (count($restaurantResult) > 0) {
    $csvData->addCols(array_keys($restaurantResult[0]));
    $csvData->addRows($restaurantResult);
}
$csvData->save();

//upload to ftp
echo "UPLOADING TO SFTP SERVER \n";
$conn = ssh2_connect('46.163.78.68');
ssh2_auth_password($conn, 'root', 'w47jA5f3');
$ratingFile = sprintf('/home/gelbeseiten/%s-lieferando-datenlieferung-bewertungen.txt', IS_PRODUCTION ? 'PRODUCTION' : 'DEVELOPMENT');
$dataFile = sprintf('/home/gelbeseiten/%s-lieferando-datenlieferung-restaurants.txt', IS_PRODUCTION ? 'PRODUCTION' : 'DEVELOPMENT');
ssh2_sftp_unlink($conn, $ratingFile);
ssh2_sftp_unlink($conn, $dataFile);
ssh2_scp_send($conn, $csvRating->csv, $ratingFile, 0644);
ssh2_scp_send($conn, $csvData->csv, $dataFile, 0644);
