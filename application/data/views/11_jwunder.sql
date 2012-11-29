DROP VIEW IF EXISTS view_affiliate_feed_citymerge
;
CREATE
     ALGORITHM = TEMPTABLE
     VIEW view_affiliate_feed_citymerge
AS
     SELECT
        c.id cid
        ,c.parentCityId cPCI
        ,c.restUrl restUrl
        ,c2.id c2id
        ,c.plz
    FROM city c
    JOIN city c2 ON c.parentCityId = 0 AND c2.parentCityId = c.id
    UNION
    SELECT
        c.id cid
        ,c.parentCityId cPCI
        ,c.restUrl restUrl
        ,c2.id c2id
        ,c.plz
    FROM city c
    JOIN city c2 ON c.parentCityId = 0 AND c.id = c2.id
    UNION
    SELECT
        c.id cid
        ,c.parentCityId cPCI
        ,c.restUrl restUrl
        ,c2.id c2id
        ,c.plz
    FROM city c
    JOIN city c2 ON c.parentCityId > 0 AND c.id = c2.id
;


DROP VIEW IF EXISTS view_affiliate_feed_restaurantid_per_plz_sub
;
CREATE
     ALGORITHM = TEMPTABLE
     VIEW view_affiliate_feed_restaurantid_per_plz_sub
AS
    SELECT
        vacm.plz
        ,r.id as restaurantId
    FROM restaurant_plz rp
    JOIN view_affiliate_feed_citymerge vacm ON vacm.cid = rp.cityId
    JOIN restaurants r ON r.id = rp.restaurantId
    JOIN restaurant_servicetype rs ON rs.restaurantId = r.id
    WHERE 1
        AND r.isOnline = 1
        AND r.deleted = 0
        AND r.status = 0
        AND rp.`status` = 1
        AND rs.servicetypeId = 1
    ###################################
    UNION
    SELECT
        vacm.plz
        ,r.id as restaurantId
    FROM restaurant_plz rp
    JOIN view_affiliate_feed_citymerge vacm ON rp.cityId = vacm.cPCI
    JOIN restaurants r ON r.id = rp.restaurantId
    JOIN restaurant_servicetype rs ON rs.restaurantId = r.id
    WHERE 1
        AND r.isOnline = 1
        AND r.deleted = 0
        AND r.status = 0
        AND rp.`status` = 1
        AND rs.servicetypeId = 1
    ###################################
    UNION
    SELECT
        vacm.plz
        ,r.id as restaurantId
    FROM restaurant_plz rp
    JOIN view_affiliate_feed_citymerge vacm ON rp.cityId = vacm.c2id
    JOIN restaurants r ON r.id = rp.restaurantId
    JOIN restaurant_servicetype rs ON rs.restaurantId = r.id
    WHERE 1
        AND r.isOnline = 1
        AND r.deleted = 0
        AND r.status = 0
        AND rp.`status` = 1
        AND rs.servicetypeId = 1
;

DROP VIEW IF EXISTS view_affiliate_feed_restaurantid_per_plz
;
CREATE
    ALGORITHM = TEMPTABLE
    VIEW view_affiliate_feed_restaurantid_per_plz
AS
    SELECT
        sub.plz
        ,count(DISTINCT(sub.restaurantId)) as 'restaurantAnzahl'
        ,GROUP_CONCAT(DISTINCT(sub.restaurantId) SEPARATOR ',') as 'restaurantIds'
    FROM
        view_affiliate_feed_restaurantid_per_plz_sub sub
    GROUP BY sub.plz
;

CREATE OR REPLACE
    ALGORITHM = TEMPTABLE
    VIEW view_affiliate_feed_restaurantid_per_city_sub
AS
SELECT
    cSub.restUrl as restUrl
    ,r.id as restaurantId
    ,cSub.cid
FROM restaurant_plz rp
JOIN
    view_affiliate_feed_citymerge cSub ON cSub.cid = rp.cityId
JOIN restaurants r ON r.id = rp.restaurantId AND r.isOnline = 1 AND r.deleted = 0 AND r.status = 0
JOIN restaurant_servicetype rs ON rs.restaurantId = r.id AND rs.servicetypeId = 1
WHERE 1
    AND rp.`status` = 1
UNION
SELECT
    cSub.restUrl as restUrl
    ,r.id as restaurantId
    ,cSub.cid
FROM restaurant_plz rp
JOIN
    view_affiliate_feed_citymerge cSub ON rp.cityId = cSub.cPCI
JOIN restaurants r ON r.id = rp.restaurantId AND r.isOnline = 1 AND r.deleted = 0 AND r.status = 0
JOIN restaurant_servicetype rs ON rs.restaurantId = r.id AND rs.servicetypeId = 1
WHERE 1
    AND rp.`status` = 1
UNION
SELECT
    cSub.restUrl as restUrl
    ,r.id as restaurantId
    ,cSub.cid
FROM restaurant_plz rp
JOIN
    view_affiliate_feed_citymerge cSub ON rp.cityId = cSub.c2id
JOIN restaurants r ON r.id = rp.restaurantId AND r.isOnline = 1 AND r.deleted = 0 AND r.status = 0
JOIN restaurant_servicetype rs ON rs.restaurantId = r.id AND rs.servicetypeId = 1
WHERE 1
    AND rp.`status` = 1
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_affiliate_feed_restaurantid_per_city
AS
	SELECT
		CONCAT('/',cSub2.restUrl) as restUrl2
		#,count(DISTINCT(restaurantId)) as 'AnzahlRestonline'
		,GROUP_CONCAT(DISTINCT(restaurantId) SEPARATOR ',') as 'AnzahlRestonline'
		,cSub2.cid
	FROM
		view_affiliate_feed_restaurantid_per_city_sub cSub2
	WHERE cSub2.restUrl IS NOT NULL
	GROUP BY cSub2.cid
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_email_first_order_time_all
AS
	SELECT
		MIN(created) as oc_created
		,MIN(o.time) as o_time
		,email
	FROM orders_customer oc
	JOIN orders o ON o.id = oc.orderId
	GROUP BY
		email
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_email_first_order_time_accepted_order
AS
	SELECT
		MIN(created) as oc_created
		,MIN(o.time) as o_time
		,email
	FROM orders_customer oc
	JOIN orders o ON o.id = oc.orderId AND o.state > 0
	GROUP BY
		email
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_email_last_orderid_accepted_order
AS
            SELECT
                oc.email
                ,MAX(oc.orderId) as maxOrderId
            FROM orders o
            JOIN orders_customer oc ON oc.orderId = o.id
            WHERE 1
                AND o.state > 0
                AND o.kind = 'priv'
                AND o.mode = 'rest'
            GROUP BY oc.email
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_details_lastorder
AS
SELECT
		oc.email AS `email`,
		oc.prename AS `LastOrderPrename`,
		oc.name AS `LastOrderName`,
		COALESCE(cu.sex, 'n') AS `UserSex`,
		CAST(COALESCE(cu.birthday, '0000-00-00') AS DATE) AS `UserBirthday`,
		r.name AS `LastOrderServiceName`,
		rc.name AS `LastOrderServiceCategory`,
		DATEDIFF(NOW(),o.time) AS `LastOrderDaysSince`,
		DAYNAME(o.time) AS `LastOrderWeekday`,
		date_format(o.time,'%H') AS `LastOrderTime`,
		date_format(o.time,'%d.%m.%Y')  AS `LastOrderDate`,
		o.payment AS `PaymentLastOrder`,
		o.discountAmount AS `LastOrderDiscountAmount`,
		c.city AS `LastOrderCity`,
		c.plz AS `LastOrderPlz`,
		c.state AS `LastOrderBundesland`,
		rr.id AS `LastOrderRatingID`,
		IF(rr.id IS NOT NULL, 'yes', 'no') AS `RatingLastOrder`,
		IF(rr.id IS NOT NULL, IF (rr.status = 1, 'yes', 'no'), NULL) AS `RatingLastOrderOnline`,
		IF(rr.id IS NOT NULL, IF(rr.advise = 1, 'pos', 'neg'), NULL) AS `RatingLastOrderPosNeg`,
		cu.`profileImage` AS `ProfileImage`,
		cu.created AS `RegisteredDate`,
		cu.id AS `customerId`
        FROM view_optivo_email_last_orderid_accepted_order lastOrderPerEmail
        JOIN `orders` o ON o.id = lastOrderPerEmail.maxOrderId
        JOIN `orders_customer` oc ON oc.orderId = o.id
        JOIN `orders_location` ol ON ol.orderId = o.id
        JOIN `restaurants` r ON r.id = o.restaurantId
        LEFT JOIN `restaurant_categories` rc ON rc.id = r.categoryId
        LEFT JOIN `customers` cu ON cu.email = oc.email
        JOIN `city` c ON c.id = ol.cityId
        LEFT JOIN `restaurant_ratings` rr ON rr.orderId = o.id AND (rr.status IS NULL OR rr.status = 1)
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_details_allorders
AS
        SELECT
            oc.email AS `email`,
            count(o.id) AS `CountOrders`,
            round((sum(o.total)/count(o.id))) AS `averageBucketTotal`,
            IF(GROUP_CONCAT(DISTINCT(o.payment)) LIKE '%paypal%', 'yes', 'no') AS `PaymentPaypalUsed`,
            IF(GROUP_CONCAT(DISTINCT(o.payment)) LIKE '%credit%', 'yes', 'no') AS `PaymentCreditUsed`,
            IF(GROUP_CONCAT(DISTINCT(o.payment)) LIKE '%ebanking%', 'yes', 'no') AS `PaymentBankingUsed`,
            sum(o.discountAmount) AS `discountAmount`,
            DATEDIFF(NOW(), max(rr.created)) AS `RatingLastRatingDaysince`,
            IF(count(o.id) >=3, IF(count(o.id) >=5,'high', 'medium'),'low') AS `CustomerPrio`
        FROM `orders` o
        LEFT JOIN `restaurant_ratings` rr ON rr.orderId = o.id AND (rr.status IS NULL OR rr.status = 1)
        LEFT JOIN `orders_customer` oc ON oc.orderId = o.id
        WHERE
            o.state > 0
            AND o.kind = 'priv'
            AND o.mode = 'rest'
        GROUP BY oc.email
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_details_unrated_orders
AS
        SELECT
            oc.email AS `email`
            ,IF(MAX(o.id) > 0, CONCAT(
                IF(DATABASE() = 'smakuje.pl' OR DATABASE() = 'pyszne.pl'
                    ,'http://'
                    ,'http://www.'
                )
                ,IF(DATABASE() = 'smakuje.pl'
                    ,'pyszne.pl'
                    ,DATABASE()
                ), '/rate/',md5(concat('hKtER55xpuemj', MAX(o.id), 'hKtER55xpuemj'))),NULL) AS `RatingLastOrderLink`
            ,count(o.id) AS `RatingsOpen`
            FROM `orders` AS `o`
            LEFT JOIN `restaurant_ratings` AS `rr` ON rr.orderId = o.id
            JOIN `orders_customer` AS `oc` ON oc.orderId = o.id
        WHERE
            o.state > 0
            AND rr.id is null
            AND o.deliverTime > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY oc.email
;

CREATE OR REPLACE
             ALGORITHM=TEMPTABLE
             VIEW `view_optivo_details_fidelitypoints`
AS
    select
    if(isnull(`cft`.`email`),NULL,`cft`.`email`) AS `email`,
    sum(`cft`.`points`) AS `FidelityPointsCount`,
    if(((100 - sum(`cft`.`points`)) < 0),0,(100 - sum(`cft`.`points`))) AS `FidelityPointsMissingto100`
    from `customer_fidelity_transaction` `cft`
    where (`cft`.`status` = 0)
    group by `cft`.`email`;


CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_details_allorders_per_application
AS
	SELECT
		oc.email as email
		,COUNT(DISTINCT(oWebsite.id)) as CountOrdersWebsite
		,COUNT(DISTINCT(oMobileWebsite.id)) as CountOrdersMobileWebsite
		,COUNT(DISTINCT(oIphoneApp.id)) as CountOrdersIphoneApp
		,COUNT(DISTINCT(oIpadApp.id)) as CountOrdersIpadApp 
		,COUNT(DISTINCT(oAndroidAppPhone.id)) as CountOrdersAndroidAppPhone
		,COUNT(DISTINCT(oAndroidAppTablet.id)) as CountOrdersAndroidAppTablet
	FROM orders o
	LEFT JOIN orders_customer oc ON
		oc.orderId = o.id
	LEFT JOIN orders oWebsite ON
		oWebsite.id = o.id
		AND oWebsite.uuid IS NULL
	LEFT JOIN orders oMobileWebsite ON
		oMobileWebsite.id = o.id
		AND oMobileWebsite.uuid LIKE 'mobile%'
	LEFT JOIN orders oIphoneApp ON
		oIphoneApp.id = o.id
		AND oIphoneApp.uuid IS NOT NULL
		AND oIphoneApp.uuid NOT LIKE 'mobile%'
		AND oIphoneApp.uuid NOT LIKE 'ios-app-tablet%'
		AND oIphoneApp.uuid NOT LIKE 'android-app-phone%'
		AND oIphoneApp.uuid NOT LIKE 'android-app-tablet%'
	LEFT JOIN orders oIpadApp ON
		oIpadApp.id = o.id
		AND oIpadApp.uuid IS NOT NULL
		AND oIpadApp.uuid LIKE 'ios-app-tablet%'
	LEFT JOIN orders oAndroidAppPhone ON
		oAndroidAppPhone.id = o.id
		AND oAndroidAppPhone.uuid IS NOT NULL
		AND oAndroidAppPhone.uuid LIKE 'android-app-phone%'
	LEFT JOIN orders oAndroidAppTablet ON
		oAndroidAppTablet.id = o.id
		AND oAndroidAppTablet.uuid IS NOT NULL
		AND oAndroidAppTablet.uuid LIKE 'android-app-tablet%'
	WHERE
		o.state > 0
		AND o.kind = 'priv'
		AND o.mode = 'rest'
	GROUP BY oc.email
;

CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_optivo_export_complete
AS
	SELECT
		COALESCE(LastOrder.LastOrderPrename,'') AS `UserPrename`,
		COALESCE(LastOrder.LastOrderName,'') AS `UserName`,
		COALESCE(LastOrder.email,'') AS `UserEmail`,
		COALESCE(LastOrder.UserSex,'') AS UserSex,
		COALESCE(LastOrder.UserBirthday,'') AS UserBirthday,
		COALESCE(NULL,'') AS `UserCustomersource`,
		COALESCE(LastOrder.LastOrderServiceName,'') AS LastOrderServiceName,
		COALESCE(LastOrder.LastOrderServiceCategory,'') AS LastOrderServiceCategory,
		COALESCE(LastOrder.LastOrderDaysSince,'') AS LastOrderDaysSince,
		COALESCE(LastOrder.PaymentLastOrder,'') AS PaymentLastOrder,
		COALESCE(AllOrders.PaymentPaypalUsed,'') AS PaymentPaypalUsed,
		COALESCE(AllOrders.PaymentCreditUsed,'') AS PaymentCreditUsed,
		COALESCE(AllOrders.PaymentBankingUsed,'') AS PaymentBankingUsed,
		COALESCE(LEFT(LastOrder.LastOrderPlz,1),'') AS `LastOrderPlzRange`,
		COALESCE(LastOrder.LastOrderPlz,'') AS LastOrderPlz,
		COALESCE(LastOrder.LastOrderCity,'') AS LastOrderCity,
		COALESCE(LastOrder.LastOrderBundesland,'') AS LastOrderBundesland,
		COALESCE(LastOrder.LastOrderWeekday,'') AS LastOrderWeekday,
		COALESCE(LastOrder.LastOrderTime,'') AS LastOrderTime,
		COALESCE(LastOrder.LastOrderDate,'') AS LastOrderDate,
		COALESCE(LastOrder.RatingLastOrder,'') AS RatingLastOrder,
		COALESCE(LastOrder.RatingLastOrderOnline,'') AS RatingLastOrderOnline,
		COALESCE(LastOrder.RatingLastOrderPosNeg,'') AS RatingLastOrderPosNeg,
		COALESCE(UnratedOrders.RatingsOpen,'') AS RatingsOpen,
		COALESCE(AllOrders.RatingLastRatingDaysince,'') AS `RatingLastRatingDaysSince`,
		COALESCE(UnratedOrders.RatingLastOrderLink,'') AS RatingLastOrderLink,
		COALESCE(NULL,'') AS `RatingBlockNotRatedOrders`,
		COALESCE(AllOrders.CountOrders,'') AS CountOrders,
		COALESCE(AllOrders.averageBucketTotal,'') AS averageBucketTotal,
		COALESCE(AllOrders.CustomerPrio,'') AS CustomerPrio,
		COALESCE(FidelityPoints.FidelityPointsCount,'') AS FidelityPointsCount,
		COALESCE(FidelityPoints.FidelityPointsMissingto100,'') AS FidelityPointsMissingto100,
		COALESCE((UnratedOrders.RatingsOpen*5)+(IF(LastOrder.ProfileImage IS NULL, 8, 0)),'') AS `FidelityPointsPossible`,
		COALESCE(NULL,'') AS `FidelityPointsLikesFacebook`,
		COALESCE(IF(LastOrder.ProfileImage IS NULL, 0, 1),'') AS `FidelityPointsUploadedaPic`,
		COALESCE(IF(LastOrder.RegisteredDate > 0, 'yes', 'no'),'') AS `Registered`,
		COALESCE(LastOrder.RegisteredDate,'') AS RegisteredDate,
		COALESCE(IF(LastOrderDiscountAmount > 0, 'yes', 'no'),'') AS `VoucherLastOrderUsed`,
		COALESCE(IF(AllOrders.discountAmount > 0, 'yes', 'no'),'') AS `VoucherEverUsed`,
		COALESCE(NULL,'') AS `1stDlName`,
		COALESCE(NULL,'') AS `1stDlDirectLink`,
		COALESCE(NULL,'') AS `1stDlRating`,
		COALESCE(NULL,'') AS `1stDlCountRatings`,
		COALESCE(NULL,'') AS `1stDlLinkPicture`,
		COALESCE(NULL,'') AS `1stDlLinkStarsPicture`,
		COALESCE(NULL,'') AS `1stDlTopComment`,
		COALESCE(NULL,'') AS `1stDlTopCommentName`,
		COALESCE(NULL,'') AS `1stDlTopCommentDate`,
		COALESCE(NULL,'') AS `2ndDlName`,
		COALESCE(NULL,'') AS `2ndDlDirectLink`,
		COALESCE(NULL,'') AS `2ndDlRating`,
		COALESCE(NULL,'') AS `2ndDlCountRatings`,
		COALESCE(NULL,'') AS `2ndDlLinkPicture`,
		COALESCE(NULL,'') AS `2ndDlLinkStarsPicture`,
		COALESCE(NULL,'') AS `2ndDlTopComment`,
		COALESCE(NULL,'') AS `2ndDlTopCommentName`,
		COALESCE(NULL,'') AS `2ndDlTopCommentDate`,
		COALESCE(NULL,'') AS `3rdDlName`,
		COALESCE(NULL,'') AS `3rdDlDirectLink`,
		COALESCE(NULL,'') AS `3rdDlRating`,
		COALESCE(NULL,'') AS `3rdDlCountRatings`,
		COALESCE(NULL,'') AS `3rdDlLinkPicture`,
		COALESCE(NULL,'') AS `3rdDlLinkStarsPicture`,
		COALESCE(NULL,'') AS `3rdDlTopComment`,
		COALESCE(NULL,'') AS `3rdDlTopCommentName`,
		COALESCE(NULL,'') AS `3rdDlTopCommentDate`,
		COALESCE(OrdersPerApplication.CountOrdersWebsite,'') AS `CountOrdersWebsite`,
		COALESCE(OrdersPerApplication.CountOrdersMobileWebsite,'') AS `CountOrdersMobileWebsite`,
		COALESCE(OrdersPerApplication.CountOrdersIphoneApp,'') AS `CountOrdersIphoneApp`,
		COALESCE(OrdersPerApplication.CountOrdersIpadApp,'') AS `CountOrdersIpadApp`,
		COALESCE(OrdersPerApplication.CountOrdersAndroidAppPhone,'') AS `CountOrdersAndroidAppPhone`,
		COALESCE(OrdersPerApplication.CountOrdersAndroidAppTablet,'') AS `CountOrdersAndroidAppTablet`
	FROM data_view_optivo_details_lastorder LastOrder
	LEFT JOIN data_view_optivo_details_allorders AllOrders ON LastOrder.email = AllOrders.email
	LEFT JOIN data_view_optivo_details_unrated_orders UnratedOrders ON LastOrder.email = UnratedOrders.email
	LEFT JOIN data_view_optivo_details_fidelitypoints FidelityPoints ON LastOrder.email = FidelityPoints.email
	LEFT JOIN data_view_optivo_details_allorders_per_application OrdersPerApplication ON OrdersPerApplication.email = LastOrder.email
;

#Opening times  of all restaurants
CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_affiliate_feed_restaurant_openings
AS
	SELECT
	    r.id as restaurantId
	    #Montag:
	    ,CONCAT(CASE rosMonday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosMonday.from, '%H:%i'),'-',TIME_FORMAT(rosMonday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohMonday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roMonday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roMonday.from, '%H:%i'),'-',TIME_FORMAT(roMonday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Montag
	    #Dienstag:
	    CASE rosTuesday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosTuesday.from, '%H:%i'),'-',TIME_FORMAT(rosTuesday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohTuesday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roTuesday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roTuesday.from, '%H:%i'),'-',TIME_FORMAT(roTuesday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Dienstag
	    #Mittwoch:
	    CASE rosWednesday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosWednesday.from, '%H:%i'),'-',TIME_FORMAT(rosWednesday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohWednesday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roWednesday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roWednesday.from, '%H:%i'),'-',TIME_FORMAT(roWednesday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Mittwoch
	    #Donnerstag:
	    CASE rosThursday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosThursday.from, '%H:%i'),'-',TIME_FORMAT(rosThursday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohThursday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roThursday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roThursday.from, '%H:%i'),'-',TIME_FORMAT(roThursday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Donnerstag
	    #Freitag:
	    CASE rosFriday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosFriday.from, '%H:%i'),'-',TIME_FORMAT(rosFriday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohFriday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roFriday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFriday.from, '%H:%i'),'-',TIME_FORMAT(roFriday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Freitag
	    #Sonnabend:
	    CASE rosSaturday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosSaturday.from, '%H:%i'),'-',TIME_FORMAT(rosSaturday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohSaturday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roSaturday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roSaturday.from, '%H:%i'),'-',TIME_FORMAT(roSaturday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END ,'|',#as Sonnabend
	    #Sonntag:
	    CASE rosSunday.closed
		WHEN 1 THEN
		   ''
		WHEN 0 THEN
		    GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(rosSunday.from, '%H:%i'),'-',TIME_FORMAT(rosSunday.until, '%H:%i'))) SEPARATOR '+')
		ELSE
		    IF(rohSunday.id IS NOT NULL AND roFeiertag.id IS NOT NULL
			,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roFeiertag.from, '%H:%i'),'-',TIME_FORMAT(roFeiertag.until, '%H:%i'))) SEPARATOR '+')
			,IF(roSunday.id IS NOT NULL
			    ,GROUP_CONCAT(DISTINCT(CONCAT(TIME_FORMAT(roSunday.from, '%H:%i'),'-',TIME_FORMAT(roSunday.until, '%H:%i'))) SEPARATOR '+')
			    ,''
			)
		    )
	    END #as Sonntag
	    ) as openingsForCurrentCalendarWeek
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
	GROUP BY r.id
;



-- http://ticket/browse/YD-2713
CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_first_orderid_per_concatenated_name_and_plz
AS
	SELECT
	    CONCAT(LOWER(oc.name), '<>', ol.plz)
	    as nachnameAndPlzId
	    ,MIN(ol.orderId) as minOrderId
	FROM orders o
	JOIN orders_customer oc ON oc.orderId = o.id
	JOIN orders_location ol ON ol.orderId = o.id
	WHERE 1
	    AND o.state > 0
	    AND o.kind = 'priv'
	    AND o.mode = 'rest'
	GROUP BY nachnameAndPlzId
;


-- http://ticket/browse/YD-2712
CREATE OR REPLACE
	ALGORITHM = TEMPTABLE
	VIEW view_first_orderid_per_beautified_telnumber
AS
	SELECT
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(
	    REPLACE(ol.tel
	    ,'Telefon','')
	    ,'Mobil','')
	    ,'Tel','')
	    ,'Fax','')
	    ,'=','0')
	    ,'o','0')
	    ,'O','0')
	    ,'ß','0')
	    ,'"','')
	    ,'´','')
	    ,'^','')
	    ,'.','')
	    ,':','')
	    ,',','')
	    ,'\'','')
	    ,'/','')
	    ,'*','')
	    ,'(','')
	    ,')','')
	    ,' ','')
	    ,'-','')
	    ,'+','')
	    as telefonnummerId
	    ,MIN(ol.orderId) as minOrderId
	FROM orders o
	JOIN orders_location ol ON ol.orderId = o.id
	WHERE 1
	    AND o.state > 0
	    AND o.kind = 'priv'
	    AND o.mode = 'rest'
	GROUP BY telefonnummerId
	HAVING telefonnummerId REGEXP '^[[:digit:]]+$' = 1
;
