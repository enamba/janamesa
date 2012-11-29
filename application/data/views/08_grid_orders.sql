-- @author vpriem
-- @since 30.11.2010
DROP TABLE IF EXISTS `view_grid_orders`;
DROP VIEW IF EXISTS `view_grid_orders`;
CREATE VIEW `view_grid_orders` AS
SELECT o.id `ID`,
    o.nr `Nummer`,
    DATE_FORMAT(o.time, '%d.%m.%Y %H:%i') `Eingang`,
    DATE_FORMAT(o.deliverTime, '%d.%m.%Y %H:%i') `Lieferzeit`,
    o.state `Status`,
    o.payment `Payment`,
    o.mode `Typ`,
    o.kind `Kundentyp`,
    o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount `Preis`,
    IF(o.rabattCodeId > 0, 1, 0) `Gutschein`,
    o.rabattCodeId,
    o.restaurantId,
    o.sendBy,
    CONCAT(r.name, ' (', r.customerNr , ')', ' ', '#', r.id) `Dienstleister`,
    r.franchiseTypeId,
    r.notifyPayed,
    r.tel `TelNr`,
    o.customerId,
    o.ipAddr,
    CONCAT(oc.prename, ' ', oc.name, IF(o.customerId is not NULL, CONCAT(' #', o.customerId), '')) `Name`,
    oc.email `eMail`,
    CONCAT(l.street, ' ', l.hausnr, ' ', CONVERT(l.plz USING utf8)) `Adresse` ,
    if (city.parentCityId > 0, CONCAT(cp.city, ' (', city.city, ')'), city.city) `Stadt`,
    l.companyName `Firma`,
    l.tel,
    o.uuid,
    pt.payerId,
    o.companyId
FROM `orders` o
LEFT JOIN `restaurants` r ON o.restaurantId = r.id
INNER JOIN `orders_customer` oc ON o.id = oc.orderId
INNER JOIN `orders_location` l ON o.id = l.orderId
LEFT JOIN `city` ON city.id = l.cityId
LEFT JOIN `city` cp ON cp.id = city.parentCityId
LEFT JOIN `paypal_transactions` pt ON pt.orderId = o.id and pt.payerId <> ''
GROUP BY o.id
ORDER BY o.id DESC;

-- @author vpriem / mlaug
-- @since 30.11.2010
DROP TABLE IF EXISTS `view_grid_orders_this_day`;
DROP VIEW IF EXISTS `view_grid_orders_this_day`;
CREATE VIEW `view_grid_orders_this_day` AS
SELECT o.id `ID`,
    o.nr `Nummer`,
    DATE_FORMAT(o.time, '%d.%m.%Y %H:%i') `Eingang`,
    DATE_FORMAT(o.deliverTime, '%d.%m.%Y %H:%i') `Lieferzeit`,
    o.state `Status`,
    o.payment `Payment`,
    o.mode `Typ`,
    o.kind `Kundentyp`,
    o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount `Preis`,
    IF(o.rabattCodeId > 0, 1, 0) `Gutschein`,
    o.rabattCodeId,
    o.restaurantId,
    o.sendBy,
    CONCAT(r.name, ' (', r.customerNr , ')', ' ', '#', r.id) `Dienstleister`,
    r.franchiseTypeId,
    r.notifyPayed,
    r.tel `TelNr`,
    o.customerId,
    o.ipAddr,
    CONCAT(oc.prename, ' ', oc.name, IF(o.customerId is not NULL, CONCAT(' #', o.customerId), '')) `Name`,
    oc.email `eMail`,
    CAST(CONCAT(l.street, ' ', l.hausnr, ' ', CONVERT(l.plz USING utf8)) as char (255)) `Adresse` ,
    CAST(if (city.parentCityId > 0, CONCAT(cp.city, ' (', city.city, ')'), city.city) as char (255)) `Stadt`,
    l.companyName `Firma`,
    l.tel,
    o.uuid,
    pt.payerId,
    o.companyId
FROM `orders` o
LEFT JOIN `restaurants` r ON o.restaurantId = r.id
INNER JOIN `orders_customer` oc ON o.id = oc.orderId
INNER JOIN `orders_location` l ON o.id = l.orderId
LEFT JOIN `city` ON city.id = l.cityId
LEFT JOIN `city` cp ON cp.id = city.parentCityId
LEFT JOIN `paypal_transactions` pt ON pt.orderId = o.id and pt.payerId <> ''
WHERE o.time > DATE(NOW())
GROUP BY o.id
ORDER BY o.id DESC;


DROP TABLE IF EXISTS `view_grid_orders_last_seven`;
DROP VIEW IF EXISTS `view_grid_orders_last_seven`;
CREATE VIEW `view_grid_orders_last_seven` AS
SELECT o.id `ID`,
    o.nr `Nummer`,
    DATE_FORMAT(o.time, '%d.%m.%Y %H:%i') `Eingang`,
    DATE_FORMAT(o.deliverTime, '%d.%m.%Y %H:%i') `Lieferzeit`,
    o.state `Status`,
    o.payment `Payment`,
    o.mode `Typ`,
    o.kind `Kundentyp`,
    o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount `Preis`,
    IF(o.rabattCodeId > 0, 1, 0) `Gutschein`,
    o.rabattCodeId,
    o.restaurantId,
    o.sendBy,
    CONCAT(r.name, ' (', r.customerNr , ')', ' ', '#', r.id) `Dienstleister`,
    r.franchiseTypeId,
    r.notifyPayed,
    r.tel `TelNr`,
    o.customerId,
    o.ipAddr,
    CONCAT(oc.prename, ' ', oc.name, IF(o.customerId is not NULL, CONCAT(' #', o.customerId), '')) `Name`,
    oc.email `eMail`,
    CAST(CONCAT(l.street, ' ', l.hausnr, ' ', CONVERT(l.plz USING utf8)) as char (255)) `Adresse` ,
    CAST(if (city.parentCityId > 0, CONCAT(cp.city, ' (', city.city, ')'), city.city) as char (255)) `Stadt`,
    l.companyName `Firma`,
    l.tel,
    o.uuid,
    pt.payerId,
    o.companyId
FROM `orders` o
LEFT JOIN `restaurants` r ON o.restaurantId = r.id
INNER JOIN `orders_customer` oc ON o.id = oc.orderId
INNER JOIN `orders_location` l ON o.id = l.orderId
LEFT JOIN `city` ON city.id = l.cityId
LEFT JOIN `city` cp ON cp.id = city.parentCityId
LEFT JOIN `paypal_transactions` pt ON pt.orderId = o.id and pt.payerId <> ''
WHERE o.time > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY o.id
ORDER BY o.id DESC;
