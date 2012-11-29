# first run update scripts in update_manual for every domain
ALTER TABLE city ADD regionId INT NULL AFTER stateId, ADD districtId INT NULL after stateId, ADD seoText TEXT NULL, ADD seoHeadline VARCHAR(255) NULL AFTER seoText, ADD lat FLOAT NULL, ADD lng FLOAT NULL, ADD INDEX (regionId), ADD INDEX (districtId);
UPDATE city c, GeoPC g SET c.regionId = g.regionId, c.districtId = g.districtId
    WHERE c.districtId IS null AND c.plz = g.ZIP AND c.parentCityId>0 AND c.city = g.Area1;
UPDATE city c, GeoPC g SET c.regionId = g.regionId, c.districtId = g.districtId
    WHERE c.districtId IS null AND c.plz = g.ZIP;
UPDATE city c, GeoPC g SET c.lat=g.Lat, c.lng=g.Lng WHERE c.lng IS NULL AND c.plz = g.ZIP;
UPDATE restaurant_plz rp, city c, regions r  SET r.used=1 WHERE r.id=c.regionId AND c.id=rp.cityId;
UPDATE restaurant_plz rp, city c, districts d SET d.used=1 WHERE d.id=c.districtId AND c.id=rp.cityId;